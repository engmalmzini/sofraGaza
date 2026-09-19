@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'الزبائن والنقاط')

@section('content')
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الهاتف</th>
                <th>النقاط</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->phone }}</td>
                    <td>{{ $user->points_balance }}</td>
                    <td><a class="font-bold text-primary" href="{{ route('admin.users.show', $user) }}">عرض</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
