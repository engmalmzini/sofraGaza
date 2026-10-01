<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReferralService
{
    public const SESSION_KEY = 'referral_invite_code';

    public const SETTING_INVITER = 'referral_inviter_points';

    public const SETTING_INVITEE = 'referral_invitee_points';

    public const DEFAULT_INVITER = 50;

    public const DEFAULT_INVITEE = 50;

    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function normalize(?string $code): ?string
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $code) ?? '');

        return $code === '' ? null : substr($code, 0, 12);
    }

    public function rememberIncomingCode(?string $code): void
    {
        $normalized = $this->normalize($code);
        if ($normalized) {
            session([self::SESSION_KEY => $normalized]);
        }
    }

    public function incomingCode(): ?string
    {
        return $this->normalize(session(self::SESSION_KEY));
    }

    public function forgetIncomingCode(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function generateUniqueCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = strlen($chars) - 1;

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, $length)];
            }
        } while (User::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function inviterPoints(): int
    {
        return max(0, (int) Setting::value(self::SETTING_INVITER, self::DEFAULT_INVITER));
    }

    public function inviteePoints(): int
    {
        return max(0, (int) Setting::value(self::SETTING_INVITEE, self::DEFAULT_INVITEE));
    }

    public function resolveReferrer(?string $code): ?User
    {
        $normalized = $this->normalize($code);
        if ($normalized === null) {
            return null;
        }

        $referrer = User::query()
            ->where('referral_code', $normalized)
            ->where('role', 'customer')
            ->first();

        if (! $referrer) {
            throw ValidationException::withMessages([
                'referral_code' => 'كود الدعوة غير صحيح. تأكد منه أو اترك الحقل فارغاً.',
            ]);
        }

        return $referrer;
    }

    public function awardBoth(User $referrer, User $invitee): bool
    {
        if ($invitee->id === $referrer->id || $referrer->role !== 'customer') {
            return false;
        }

        $inviterPts = $this->inviterPoints();
        $inviteePts = $this->inviteePoints();
        $awarded = false;

        DB::transaction(function () use ($referrer, $invitee, $inviterPts, $inviteePts, &$awarded) {
            $locked = User::query()->lockForUpdate()->find($invitee->id);
            if (! $locked || $locked->referred_by_id) {
                return;
            }

            $locked->forceFill(['referred_by_id' => $referrer->id])->save();
            $invitee->setAttribute('referred_by_id', $referrer->id);
            $awarded = true;

            $points = app(PointsService::class);
            if ($inviterPts > 0) {
                $points->adjust($referrer->fresh(), $inviterPts, 'دعوة صديق: انضم '.$invitee->name);
            }
            if ($inviteePts > 0) {
                $points->adjust($invitee->fresh(), $inviteePts, 'هدية انضمام عبر دعوة '.$referrer->name);
            }
        });

        if (! $awarded) {
            return false;
        }

        if ($inviterPts > 0) {
            $this->notifications->notify(
                $referrer,
                'صديقك انضم عبر كودك',
                "{$invitee->name} سجّل بكود الدعوة، وحصلت على {$inviterPts} نقطة. ادعُ المزيد!",
                route('account.invite')
            );
        }

        if ($inviteePts > 0) {
            $this->notifications->notify(
                $invitee,
                'هدية نقاط الدعوة',
                "سجّلت بكود {$referrer->name} وحصلت على {$inviteePts} نقطة. ادعُ أصدقاءك بنفس الطريقة.",
                route('account.invite')
            );
        }

        return true;
    }

    public function shareUrl(User $user): string
    {
        return route('register', ['ref' => $user->ensureReferralCode()]);
    }

    public function shareText(User $user): string
    {
        $code = $user->ensureReferralCode();
        $points = $this->inviteePoints();

        return "سجّل في سفرة غزة بكودي {$code} وكلاانا ناخذ {$points} نقطة.\n".$this->shareUrl($user);
    }

    public function whatsappShareUrl(User $user): string
    {
        return 'https://wa.me/?text='.rawurlencode($this->shareText($user));
    }
}
