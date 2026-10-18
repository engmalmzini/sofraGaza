{{-- Sofra letter-by-letter splash. Homepage only. Brand colors, loops until the page is ready. --}}
@if(request()->routeIs('home'))
<style>
#sg-splash {
    --bg: #d75600;
    --fg: #faf6ee;
    position: fixed;
    inset: 0;
    z-index: 2147483000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #d75600;
    overflow: hidden;
    transition: opacity .45s ease, visibility .45s ease;
}
#sg-splash.is-done,
html.sg-splash-skip #sg-splash {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    display: none !important;
}
html.sg-splash-on,
html.sg-splash-on body {
    overflow: hidden;
}
#sg-splash #sg-stage {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #d75600;
}
#sg-splash #sg-logo {
    position: absolute;
    left: 50%;
    top: 50%;
    width: min(86vw, 260px);
    aspect-ratio: 730 / 311;
    transform: translate(-50%, -50%);
}
@media (min-width: 380px) {
    #sg-splash #sg-logo { width: min(82vw, 320px); }
}
@media (min-width: 480px) {
    #sg-splash #sg-logo { width: min(72vw, 380px); }
}
@media (min-width: 768px) {
    #sg-splash #sg-logo { width: min(54vw, 460px); }
}
@media (min-width: 1024px) {
    #sg-splash #sg-logo { width: min(40vw, 540px); }
}
@media (min-width: 1440px) {
    #sg-splash #sg-logo { width: min(34vw, 640px); }
}








#sg-splash .p{position:absolute;background:var(--fg);-webkit-mask:var(--u) center/100% 100% no-repeat;mask:var(--u) center/100% 100% no-repeat;opacity:0;transition:background .4s}
#sg-splash .go .lt{transform-origin:50% 100%;animation:pop .7s cubic-bezier(.3,1.4,.5,1) var(--d) both}
@keyframes pop{0%{opacity:0;transform:translateY(60%) scale(1.3,.15)}55%{opacity:1;transform:translateY(-14%) scale(.9,1.18)}78%{transform:translateY(0) scale(1.05,.93)}100%{opacity:1;transform:none}}
#sg-splash .go .ob{transform-origin:50% 50%;animation:snap .5s cubic-bezier(.3,1.6,.5,1) var(--d) both}
@keyframes snap{0%{opacity:0;transform:scale(0) rotate(-120deg)}100%{opacity:1;transform:none}}
#sg-splash .go .ar{transform-origin:100% 60%;animation:swoosh .75s cubic-bezier(.2,1.2,.4,1) var(--d) both}
@keyframes swoosh{0%{opacity:0;transform:translateX(45%) scale(.55) rotate(8deg)}60%{opacity:1;transform:translateX(-3%) scale(1.06) rotate(-2deg)}100%{opacity:1;transform:none}}
#sg-splash .go .dot{transform-origin:50% 100%;animation:drop .8s cubic-bezier(.3,1.5,.5,1) var(--d) both}
@keyframes drop{0%{opacity:0;transform:translateY(-600%) scale(.6,1.4)}55%{opacity:1;transform:translateY(0) scale(1.25,.7)}75%{transform:translateY(-35%) scale(.92,1.1)}100%{opacity:1;transform:none}}
#sg-splash .go .ln{transform-origin:100% 50%;animation:zip .55s cubic-bezier(.2,.9,.2,1) var(--d) both,nudge 2.4s ease-in-out calc(var(--d) + 1s) infinite alternate}
@keyframes zip{0%{opacity:0;transform:translateX(-30%) scaleX(0)}60%{opacity:1;transform:translateX(0) scaleX(1.12)}100%{opacity:1;transform:none}}
@keyframes nudge{to{transform:translateX(-7%) scaleX(.94)}}
#sg-splash #sg-spark{position:absolute;left:42.74%;top:83.28%;width:15%;aspect-ratio:1;margin:-7.5% 0 0 -7.5%;opacity:0}
#sg-splash #sg-spark svg{width:100%;height:100%;fill:var(--fg);display:block}
#sg-splash .go #sg-spark{animation:spark 1.9s cubic-bezier(.45,0,.55,1) .2s both}
@keyframes spark{0%{opacity:0;top:-160%;transform:scale(.2) rotate(-90deg)}
8%{opacity:1}
30%{top:83.28%;transform:scale(1.15,.8) rotate(0)}
44%{top:35%;transform:scale(.85,1.15) rotate(90deg)}
60%{top:83.28%;transform:scale(1.15,.8) rotate(180deg)}
74%{opacity:1;top:83.28%;transform:scale(1.5) rotate(270deg)}
100%{opacity:0;top:83.28%;transform:scale(0) rotate(400deg)}}
#sg-splash #sg-burst{position:absolute;left:42.74%;top:83.28%;width:30%;aspect-ratio:1;margin:-15% 0 0 -15%;border-radius:50%;border:4px solid var(--fg);opacity:0}
#sg-splash .go #sg-burst{animation:burst .9s ease-out 1.5s both}
@keyframes burst{0%{opacity:.8;transform:scale(.1)}100%{opacity:0;transform:scale(3)}}



#sg-splash #sg-stage, #sg-splash .p, #sg-splash #sg-spark svg {
    --bg: #d75600;
    --fg: #faf6ee;
}
#sg-splash .p {
    background: #faf6ee;
}
#sg-splash #sg-spark svg {
    fill: #faf6ee;
}
</style>
<div id="sg-splash" role="status" aria-label="جاري تحميل سفرة غزة">
<div id="sg-stage" class="go"><div id="sg-logo">
<i class="p ar" style="left:59.726%;top:24.437%;width:40.274%;height:37.621%;--d:1.85s;--u:url(data:image/png;base64,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)"></i>
<i class="p ar" style="left:32.466%;top:16.399%;width:35.068%;height:58.842%;--d:2.05s;--u:url(data:image/png;base64,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)"></i>
<i class="p ar" style="left:14.110%;top:18.328%;width:20.685%;height:48.875%;--d:2.25s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJcAAACYCAYAAADpyXusAAAIgElEQVR42u2dzU4baRaGH6xs00a9b9nqfeSIfcuI/YjGN8DABQyCC+gILmBa5AKguYGk0exRrNlHlHrfstX7URguIL2oU6SAsvHfV/Wdz++ziSMlUD+Pzznf/8bXr18RtIG39nkL2LTPffuzC3Tm+HljYFT6+7D0+Rb4E8hSf6gbayhXzwTqmjxbwOuGrmVokt0Cn1MTLnW5OsCOSfTWxIqdsnAfgDvJFU962wF2ge05U1msZMCViTaWXPWnuUOLTj3SphDtwkNE8ypXBxgA+2sg1CSugGvgo+RaTcobAEdrLNSklukpcCm55qcPHFsdJRxJFrNcB4pSviWLTa42cGK1VEeeLMUQeMfjDty1lKtj37YBzXVoplz4HzXRumxariJSHUuq4Klyv+4o1qRcp5Kqds4sVSYr14HdoGqqZsgsigUfx2zV3KUwIu9dlljN0QP+a1nDfeRqA+f2bRFxcW3v5c6jXHvWWlFdFXea3CXAoHiotNgBficfyZdY8afJPwjQWR1CrmO7WA3X+OG11WF7sabFtuXwvt6Vaw5Z0dDRqiLXHvCXxEqCC/LuoijkOldtlaRgS6fIZdJix9KgZi2kyT3wE0t0ti4qV9ERp2iVNpmVOgv1gy2SFg8k1trQs+xUS811YPlYYq0Pfaurg6bF39AQzjozdxfFrHJJLAH5wuKZC/xZ0uKpxBLGXPXXS3IdAL/omQqjmI6+dFosinchytwDb5hhFkVrSgtBYokqXs/aeqyKXG0r2jRbVExjwAtbCVRFrnOJJWbgVwtEM8u1p5ahmKO4P5k1LbbJp82o913MU9z/wISxx3Lk0lx3sUhxP3gpcnV4vEGsELMyJt9f9hmv7M9TPaOHB1X+kmXAF57PsNWM28e1V5+KrQKKyLVuWzrfk++ePARuTKBsgYfaJd+DtYOfDX1DcA38XCVXj3zn4HWISucmU8il7MUerbusV5dOlye99i2+beifqlDvLap0yftmQu+RkJFvWdS133u1JnKdVEWuPvApQali2sKxY9Es5V197oHvJnVFpEBGvv98l7g2oB2T7+zzhiWmDTvolug9lWuIs83zJ3xrDi0NDSO+zrEVvtsJPPMqBlWRy3NdcEbeS3zp6JqH9i0fJibXblVXRLt0w55SYC2bmAXmlLQmZG5iw0FF5Loz67y8qPfkHXcpnPL1zlJ6KuxUFfRjq1nOIq4HrqxYb2R34oBcJiTY7tO0WMWeyVb0RDfBFx6fR5hiEVzmGPi383t4GGvc0Emx0fE7/vc26wLjlt5ldOwnEKG7T2suEQd3CUSuLckVL5m1iD13R6jmihjP086HwLYiV9zp8UiRS4RkhM95YRuKXPFz7vS6e4pcqr1CoZrLSe116fHCJZcPLiSXCEWGv177LcnlB2/Tozcllx8+KS2KUHyWXCIUY291l+TyxUhyCSG53JFJLhGKL5JLCMklAnIjuXxxq8glQrEpuUQoup4aH5LLF56mO2eSS5ErBGOlRX9sObnOkeTyx2vJJULgaWM+pUWlxGDcSi5feNqc5E/QimsvtPEzaP2wH70ilw8Gjq71YTq25FJKXDVDyeUrJXqS61ZyKSUqLQpXB9o/WqEkueKmj69TaR+tCpdccePteOhP5b+onyte9oAPzq55Q5HLRwvR20lyzzZKkVxxcoa/nQSfHe+ntBhnEf/J4XV3qThAXcSVDv/j8LorTxuWXPG9JI+HGlTWh5IrHn7D14TAgvtJrVrJFY9Y+06v/QMTDlaVXM1z7FgsmLKNuVqLzXIO/Mvx9T+cClvFK71fpcIlmDo8pchVP21rFfac38fDdOZJqOaqlz3yc3x6CdzLry/9A0Wu+qLVeQJpsBy1fpjUSlTkqrc1+FdCYhVR606RqzkOgHf4PIhz6ail1mIY+taK6id6fzNFrRgjV5982Xqfb7vojcjnCt3MelMN1VSHlvp6CX9xxnZ/0cvVBnaAt8w+V3xYEi2L5MtwkFg9NY1D5jhYtE65OiZT34Ra9ht+b5Jdky9nymr6QgzsHrYTrKemkdl7Iwa5yiluh3qmkgwtjWYm3O0SqbRtD/N7E6mfeMp7iW0qZpvWIVfxInaIcznUPd8Wa454PrFtsyTO5ppLVMV74Gje/7SoXJ0n9ZJehor4hbsiek/qpY6e+dqwv2hp8WqKTAP8rfgVq0+Hw0X/89O0uGe5VUKJhdNhVeQ6AC70TIWxy5Kd1i2JJSo4ZAX9hkVa/D8+lzSJ1XMF/HMVP6hlUUtiCSxaHa3qh7XwdRKWCMf9KuqsqppLSKyfqFiSL7nEsuwTYOC/hbOjbUWQluHHED+4aC2O0JDOuop1GeqHF2nxWs957XgfUqxy5OopPSpihZJLqVFiBUuLkK+vExIrSOSCfC9OzYhIj3vy7oaPdf7Sp3J1gD/QcFBKjMl73mtfLdWquJB/6H0kQ7GbTiPL8FoTLuhQ7yWJroZtGlxIPGmac1H0aY6Xz/rqqM7CfV65JJhPMquvxjFczEsD15dKkW44I1+ZNY7lgmZdt9gjHyJSJ2uc0SrIrIbQkat8A4VgIp7aqohWWYwXOM98rjvgZ+BE77VxroA35JvLRcuiy/l7doNaxl8vQxNq6OFiF52JWmync2LhWYRlbA2rbS9iLRO5yqS2U3FsUp0SQZ9VU3KVU+WxJFtZC/Dcq1Qh5Cro2LdtgAbAk66pmpCrnC5PLJKpf2x66rsiHwkZp3Rjde2JWmxMq2j2uDvhMpUo1aRcZfbIx7/WTbTiRNUhUw7AlFyr48BE20lUtMxkuiDSXvSU5Xra2iy2xvQqW3n78pvUaijPcnmTrdgheki+LO/zusvkSa5JDYMfyXfmKY5wCT0ENSZfdjcqfb6RSOnJNS3Kbdrn73l80sMkAUcTBLkF/sdyByQI4G/nk2Fw9jCc5AAAAABJRU5ErkJggg==)"></i>
<i class="p dot" style="left:18.356%;top:3.215%;width:5.479%;height:12.219%;--d:2.55s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAmCAYAAAC29NkdAAABvUlEQVR42s2YUU7CQBCGPxpfscZ3A+ECeAFDwrshcgGiR/AAJnoDb1AvgHABg/ECcAFS4gWEHmB8cJps2lIXCnT+pGnStLtfZ3dnd/6GiFBRXeAC6ABt53kMLIEfYLFv42d7fBMCQ6Cn96bHNwkwBqbAB7D27k1EfK9QRJ5FZCPVtNF2Qp9+feEOAZZVLCJ3VQFDEZnJcRWVMTRKFkkX+PKcY1W10Dmdm5vbAE8J50JeZx8GBS+2aoBLgxL9BxhqKjg1XKoRcF8G+Kh/UqdeNVA5wBbwRP1qaqByi2QCDLChBLgC1oETPStwaRT77hAPsaeBO8RzA4ujaJjPGyLS0qORRbWDzBnOJOClYUCCov3PkDoBxmUdcGkdMA60iLGqleUITtM5+KlZ2ywgWrNa09gFnBqDe0sLKPc8GOuxy8QWB6yyeXBkBO4lhSsqO2dan9alXH2cBQyB75qqugS4yTph2Ty41pfqSDs5uG178QK4PSFkAjywzUMsMW66IjI/snE0134quVvRkeAmPh6hrz/YO6ANN9P2OKSB6YJGaj7u6qpGu4D5+IM+blSfPwO95+wAsbMzrbSkfd+3k19wTi+gws2uGgAAAABJRU5ErkJggg==)"></i>
<i class="p dot" style="left:24.658%;top:2.251%;width:5.479%;height:12.219%;--d:2.65s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAmCAYAAAC29NkdAAABo0lEQVR42s2Y723CMBBHH/5ehQ3CBqm6ABILILFABRvQBSq6AQu0sAAVCyBYoIINEnWBQge4frlUbsofhwC5J6EIBPhh48v51xARKtIGHoCm9xxgpdcUWADZOV/eOEMwAnpAF+gAd4Gfy4A58ApsgkcTkdBHJCIjEdlJdSYiEoeMGyo3vJBYkXFVwUh/7TVZ6jilBRMRWcttWOt4/zwObZII+CyxAS7BRivA1n/RHZBb3VgOINFdzinBub65DtrA+Fgd7GudqptWXthdYWmfscFo3xI/AbERwcfcxRccYouBL9iuYdeeousL9rBHAsTOtzVIJxeMjQq2nJYXszjg3rBf4rBN07rgxnmHG4t8WZ/BtfOaRYt85IJTi/8/IMsFFwYFp8WGNTV2R2n5M/inSTTAS95RF1v+pZet1EWmncx236HJQss/9I+eRcGVTm+dS/seEh5N5PZMykYfy7rlQsKj8ZXFdiLSrxq/9a8UvaWHAqNz8sFLhpfpqVkLSbeOxb8DPViXyW++gZlWibdrZ9R4B62O3pJivf72cV6HNKvSLf0Ax/VThovXv7MAAAAASUVORK5CYII=)"></i>
<i class="p dot" style="left:54.384%;top:0.000%;width:5.479%;height:12.540%;--d:2.75s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAnCAYAAAB9qAq4AAABqUlEQVR42s2YUU7CQBCGv258NXgBU26A8QIkXgDhBj2BiRcwnsB4AYMXQLgAgfhO4AYQL0DhAOPLYNa6YGkLnT9pNtnS9ssMuzvzRyJCSbV1vAWuvPk1MNNxUfTlFwWeiYE7oKPjZY5nVsAEGAEfR31NRPJesYj0pbyWIpLk/W6eHzUqAguBdssCtkRkLqfVc1HAREQ2ch4NNVO5ARM5v4Z5AdtSn/pZniizD7aAz5xbx6nU87eiLODE23jr0ha4BlIA593oGoBDs/cYiuBSTwkL+omi86JnBW4XxZ6f4g721PFTvKl55e5T5HRhWIQDaDut48zKZYpMa7pzxlZvMIJN64BLw3xrp/2CVc0cxuWAuVG2LTB1wNgo4GAXwRSYGgQc+cXCyGB6xz7gm05a0Uu2ok510oJWwFOoom4AXwYqm19Nk78PpsBDzXDvf8ylQF/8WlNPHGzcoz3+4PDMbcBCC+c0dJKEdK/hPldag3B5/MFTezRJFf5grP+Pqj2YuCoD0zeVyoBujgH7b5EcUsPzqG/UcNp3XM10AQyKnvdFAA+Z682qC49vlCTvxS6BUh4AAAAASUVORK5CYII=)"></i>
<i class="p ln" style="left:6.438%;top:26.045%;width:8.219%;height:8.682%;--d:3.0s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAAbCAYAAAAgVez8AAABUklEQVR42uWYy03DQBCGv1hcLUvckd0ASpQ7ipQGAmkAQQGkA6TQAIICQGnAKA0gIhrAHZASUAowB3Yks/JjDRyYyX+xtLs+fPPPPmYGZVmiWCmQBaybuu/z4BfAk5qxQ2D0w399jYH4D4OzA44PAhcnwByYuWjFCrNhAWy7HE6AG+AK3VoDpwBtwGfArdsnmrUDjoAPgKaUvgAesKFzgQWIjMOugKfqgJ/SE+DFCOy3VG5yeIUdLXxYH3hp4IASbYDHuglJ6RR4N+TuCCjqJsThuSHY+ybYqsNvwNDqQeU7nBqBBcjbYAU4M5TOm64FEbaU7xPwtiudBdjKdRTEEbnIFOyJokq9qF3jPsC5AeA45GkswIV7oWjXtM8pfe32s2Z1Ngf9engIvKKzSSfK2ozz7+ECOFHu9LKPw6IEuHNVVGzJ5ZA27SVf/eigPfLfK6ZPCfpSRPX7THMAAAAASUVORK5CYII=)"></i>
<i class="p ln" style="left:0.000%;top:40.836%;width:7.945%;height:8.682%;--d:3.1s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAbCAYAAAAtS5y7AAABLklEQVR42u2Y0W3CMBBAX/yP6AIVbBBmYIOyQGECVAYAwQZdoKULtGIBRqjoAlUQEwQGOD64VC51CD+VnCNPshJZ/siTz/HdJSLCFbSBnr73iYO1PjdAXrU4qRAdAWMgJW5WOl5LV4hIaDyISCb1I9Nv/+MUkpxL/XmqEn0WO/yS9c/oCHjBFgPgw/8ZtYEd0DImugW6AE4nJgYlATrA3N/RTCct8gX0EhFJ9dK1TNdFlOn8u+jdDYj2HTdCI9qINqJRs3FeAWuZ7yIz2htNAX/y3SJ03w3v5puf63Y037XGAbgHcudt78Kg6KxonJ03x5bAo6GQHZZdL+Mipi1JhkRzXbDQ+K7jmZycS4ZCN1SdD2pw9Ww59XWnlDSzkys79anWrbGVdBnwqV2EixwBnXTk/VdFBnwAAAAASUVORK5CYII=)"></i>
<i class="p ln" style="left:10.411%;top:41.158%;width:7.945%;height:8.360%;--d:3.2s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAaCAYAAADmF08eAAABJUlEQVR42u2YwW3CMBSGP3KvQL1X6QIoWaCiG6CyAOoITICaBVBG6AS0CyCxAdkgWaAqMMDjwKNyIgfUW/zCL1k+JJdPnxPb/0BEuJE34BVIgJHOXUgFlDq+gfXVt0WkbbyLSCnh5CAiH208A4/RIbDtkLn/pgDmOv+lCRorZEzYOQIvLqwLGrpJH+xYv2Ui50FuCBLgAfhsGk2AHTYzA9YXowvsZukaPahqq3mOgIlxSIBZBDxiP6MISHsAWtte7qB30MBAd30B/ekB52+vDgwAG8OQBVBdQFeGQfPmffQLmBq0mfou3oWBdsHbMrj76F6NHo1A1nojXzkWc64PEwsmr52MKl3XWYB2M+CpCdlm1M1Qq4gp5/J60sGfTakrcKOSvDkBEzAsCGNG3XIAAAAASUVORK5CYII=)"></i>
<i class="p ln" style="left:4.247%;top:55.949%;width:7.945%;height:8.360%;--d:3.3s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAaCAYAAADmF08eAAABOklEQVR42uWYwU3DUAyGv7w7ChukC6BU3KsiFqiUBYrYADboAiAmABagYgNQJ8gCqNmgbQYwBxzJCq9F3BK/X8rl6R3y5bcd25mIcEI5UAEL4Bo4Y3j6BN6BN6A5ektEYk8uIisROci49CIiRYwpizha6BcqGadaYAms7WEftAQ2Aw3R/+oWeI6B5kCtjnrRVJkI5vDJGSSagrkFLTWuvakA7i3oHX61tDl6cFKAjuZqAObOIQGqQCIKwGUCnPMAnKfiaDKhu02AcxeArwRA6wDsUnC0axi2Dvtcq0kwza9XNUDTgT7owOpRK/t7aYBHj0WoG777G4YP7X09qAUu1MRfDcOim8gdQM4wW8E+6F4dfR15uM76hsVawD1wA1yNzN1WtwnT2Htnfyyw4WfNUg04d2utLetTl74BLOIis4mGlOkAAAAASUVORK5CYII=)"></i>
<i class="p lt" style="left:22.466%;top:79.421%;width:6.986%;height:20.579%;--d:0.85s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADMAAABACAYAAAC3F09FAAADh0lEQVR42tWbS07bUBSGPzxv3Q0gR2zAbKBK1DlCYQM0LKBVN1CULgDJLKCEDdCgzpEj5ijeQJWo84okC7gdcCyM5fe913Z+yYoH8ePPPY//nHNzoJTCMDxgIOdHifMkHuRzYfLBBwbIeMAZcAoMG1y/A56AlZB7ANaN3kQp1fSYKKVWyg5WSqlAKeXVeacmJKZKqa1qD6FSamyazNDiSlQlNTRBJlD9wUwp5Wa9Z1kAcMUpffqFHXCSjoZOyUV9JALwDgiBaVUyQU+JJPFd3rMwz/jAkv5jBxwCm6KVCdgPnMRE8shMGmbytvEjHQCyzGy5B76yAEZl2swTjaRrx0/ywGc5TyIWnx5wrusnRdpsYiuh5RyuPDOs8YxhVQUw1yAy1xCtiKgs033TOnKmqfba1lyRJoo8rCs0dcwLC0dsgtsq5YBjKLqsLUWtG4lah1We4aSyflOcWw7FmypfMrUyHvCt6+STzjO6DYGR6SZFHTiGbT8UXef1gczKwD2/yH3mwLhLMpHBe58Cd8AWmImAddv0Gc/Q6pSJxHshurZJBvkVz1uyjLUQ+2nCKrLIDMWR24Y2sbyyOey4QIuJXdUxxTwyLvBXuiB9KMQuq+Qvp0A+fJRCqGvEZh+WSa4iORP1iFBM6rFINpVps0gU632Pmn9XEnEpK5u7GmE0QVC310xOK+pUjq5xITVPYTSrApfXiVlXxN52agyVt3GXZd6BuU11zKzKin2S1TprIVftgPe6ZlYnpMbm6Nn0nTbIkOozXFggdgt8dlp22Aj4Ku3ZC4Ml9qAtM6vSDAk0I+IaGPSBTIy5JqGDKmTGwB/DJXVeFHzWMbUynxlKefvYQnNiI45shYwP/E4IvDtS011LRVlTrIoy+qpgdOFZapTrKAjyiCwrjDCmhscYvubGoUwys5o3mRjSdksNMmEWmUBj2BTIr9tkRZaaYnOWFpoTafOY6qw88zK5/pfI9D7wgZch7ZCXRrsJWfNGm3XVKzOFD8DGSYXgfcQtie0mR+w3LpPdmV89aynVwXUy0SYDgC+O6+0JkQg4zuubRUJosQdEdlkK28kQeyMJdbser0jmKD1PaN7IBdc9I7KQNLJp2tH0a0ocG9iW7Zupu6/ZlRu23aKdVVXpTcvmsTjgyFL0iyQZ1pp7mvpjQ9z0O25ILt5wF2FhDGiC4ID8v5zA6y7BJRX3xpThP0UoHpcN+KI4AAAAAElFTkSuQmCC)"></i>
<i class="p lt" style="left:52.740%;top:79.743%;width:6.301%;height:19.936%;--d:1.0s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAA+CAYAAABUbymsAAAAmklEQVR42u3YUQ0CMRAE0DlyAnCChXOEBqQgBQfgABycg+UDFFxJjyZv/pu+NJt0slNVXTJgpqqqEeGHDBpwcHBwcPBdMjecfSRZR+wqS5KbUQEHBwcHBwf/067Su2Sdv3c2t8NT50c+mnFwcHBwcPCtafnyr0leHa3PX5WsJRZC4ODg4ODg4ODg4ODg4ODgzZnzWTNsyX1P+Bt3nB5j6HWlGwAAAABJRU5ErkJggg==)"></i>
<i class="p lt" style="left:66.575%;top:79.743%;width:7.534%;height:19.936%;--d:1.15s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA+CAYAAACP1IOOAAACYklEQVR42t2b7U0qURCGH4x/zRoawA6gAbN2QLgN+FGARguAYAEaLMB7aeAmNmDYDtgGDNuBxgLGH5xNcIOyu3OA2TMJf8ieQ57Me+bMx9ISkQi4Y7u2AN5KPJP5/NGWiMTADDuWAXPgBXjVAB9gzzpAH3h23pwBcShwRcuVNa8K2QS43LoOchwiXG5D58UoRLjci8kmwKbCrQIGCZcDTkKFA7gGBqHCATyGDNcBLkOFA7gpfnGo2Cxdt+EGawO9wnd9Fxh8BJfOt1xURGKpZzMRwdMnEpGx6O12dV8rsvwARsCVcp++5TP3F5gq1p9YDygjZdQ0DZcpvRdbvwq8tBuswi1ChnsPGa4XMlzXh6Qtd7/Uwcgi3ESxNrEsy0ul116swk1YNmI19uqr5NFY5CJi23nqrJg61TxrqS+4GBBDnj8PtRJPWNPmCwXupkmXeBW7Kp61UOCmrsAlNLgpcNHE3FIN1kS4T5bz+4syDx82CCx1F37pKv2gId66dxlNpfZDEzx3+lOoD8FztRu1Gs+VacEdu3OiSYqvgf9smKKutR3MCiIR+aecASzcPpVmELuQ5YcL3alij06dCn2XZ67vIp+mpBlYhcvQzQHyzCSyCAfL2XWiWH9EhTnCPq6Ccw/yHliFy1zGsXV57usSHymjZyl57jND8SHPW6twqQd5jn/LfvadW25VnhYSZ60845/kaQFua/K0UvI8oJuDr5WnpZdsfMhzbLVYTYAn5R5DVqay1irxIfrXNKZW4XzIs5vL02IPJQH+KGu/IdBtuT8q9Wps8K68gDdZ5CBPaq6ffwHu2vS0raOgSQAAAABJRU5ErkJggg==)"></i>
<i class="p lt" style="left:80.274%;top:79.421%;width:9.178%;height:20.257%;--d:1.3s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEMAAAA/CAYAAAC7KnXJAAADRUlEQVR42t1c0W3bMBB9JvLrKhsoGxjoAgq6QJEu4EoDVHAWCNwBGrgDJPACrrtBtIGDLhCh/0VbD8B+hGpTW5aouyMl6gECEgQyqKfHu3fHcyZaawwECYAnAGVfC1CeH3YHQJ+4HgwZ1e9bAKlPMiaelBED+AZgSrh3DeD9mJSxJBIBAHNfCvGhjNjIn4MSwMUYlHEtRGgaOhmR4EO8DZ2Md4xYUUdGHDIZNw7IDZKMKwdvch4qGbmDz5yZKygyYuM4XWARGhlLx0E5CoWMyHGgmwp5Fy9kZILp1GsgdUFGDvdw4kilyUhdGyOXpKsQ5NuQZpOhkkFd3B5AMYQ0K0kGdWH3AFZDqFek+hkRgJ/Eey9Mv+KJ+GCfpeKHlDKoeb/AvwYwVR2plAmTIoMaONcvfr5jmLBsKGRQ02lp4kWFXwfkeE+zEmTkAqqocNunCeOSkTBK6rpt8WiuXjyOEtgiVFWcOjlbMV4My4RxUivnCOCyxWj9Rg8HThxlUCN4aeE47xlbJe6DDKrjtGn8fOrhJZHJSIky3gPYCKmn6SVFPsmgHgFsjJ+wATWQTkHstFECaILn8QFOHWILar1COptVHmNFge6DKFRHGuP53MapMjjpNCNkCU41XJgU7kwZ1Oq0JKZLTr3S2R2fdXxLVMe5Y7hDzozXoosJ67JNUkaZ3SfObTNYl21ygzBxLa2MK0uzNETsAbySVEaOcDG1jXU2ypAYUOsbVibMRhlLhA+rEYk2ZUQAvsP9QbIPtJqwNmVkIyGiMmExh4wc48KSSobPE3VfaJz6aSJjjvGhcernVACdmXpijDiZZs+EexYVvppq84eDh3ltgiF1fLo6cDquorXWh1ekeUhrPtPFtWWscVf3mUqwZwEAH0Fv83cFJ9PVDtYo4cB553nvrxn3L9rI4KTTNfx/2Y6jwqOpHyWoilv4RwH6QfVRSFAHdjXpaVEcrBj3/jf1ow7+AMYW6Qsb08ChmrDs0HRxehZevkxnoY4P3PWrF9VpiKqowDmo/jv1UymDOg8BdOg+O8aW4UoLAJcK9BP1ShVDIIKbzRIAM8VIp48D63cUzC2bTbTWD+j+3wl2AL4MtCpNALypsd/nbS/3D1rCqwTHC7l1AAAAAElFTkSuQmCC)"></i>
<i class="p lt" style="left:36.027%;top:82.637%;width:9.178%;height:17.363%;--d:1.45s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEMAAAA2CAYAAACcJSQBAAADIUlEQVR42uWbXW7aQBSFP5BfkbOByGygIup75IoNpLAB2iygFSygFd1A1Q2UsIFWbCAC9b0yG6hAfa9IWMD0geuIuhgbzzWYyZUsC4X54fjMnTPHNzVjDBnhy/0Bx6OWA4xI7qHrgNQz/n4HtOSabbHk2YHxFuhtfW4BX57jMgmBaUqbMfDmuTDDlx+cFj1XGbILjAEQZLR7J8vI6WXiA7+BRs72t8DIVWZ8OgAIgK+SX5xjRgAsCvSxBq6BuUvM6BbsowH8kK3XGWZElj9oCTRdYEag8GQXriyTrkJfH10Bo2fZz0wuJ8CwXSJjV3SGLRDrMxdevqhp3wMuLDv7eaYgBMBQ8mUD+OUBbYV8cU4RWxNJ5dz2chzKsuL+TFhwC/T3HDdCT0EoVZkZHWHBTZ4vew7aEr6w4P2BrL/wlAavilHcknNSo0jbusIErirEiklBIJ50xsyR5dGx3AyWGszoVgSMV7YHzTqwsuzkpiJg2Dpu87qCggwUtIqGjrA9Vqy0csapl8pAoY/72OmKsHe5WifaYg919NOiFifQhQJNBydkhS0Qk20/Y6IwqT7HN4VbwAclffKPIfyogPCc45Uu+JLvNB5AM6kzvik9qWO5XmMlIGaS88AYE18toxdTY4y/1bfm5Rtj7hTnGsZ9JweKFAeJjDFBCUBEyg+NNDA6Rj+GSkD0jTGPynML94GBoKUdCwElKMCEvrTXjmlyvF2VOwHlvh2bS9KaAn9krKWMG7tubdmVynrDvwZePCXOWHWllDENlfbvqsYA+PyfBN1T+riowAGsLAPo9U49vgcMGwutqrEGLtNEYT1jbfccA+J6nzrOcrq+s3GaXQFinuWBZsXozAHJXWaV1wMdiYGzdhWIQ8CIl8wl5+Omz0RL5C68O9Qdf2DjQg8qDsRA5rk8yOrK8S8W+7beScW0SLwDFirDrFsO3JTkeuoa0KWw4cpmLjbMSEYoMj48cl4Yo1Q5pAnGNihduZflicYAqCbzMsBInoDbbN66tQtK+zWbgpgZmxdepe1mZYORxhyAl2zqyQLZpVZb34nkeL86Zj76C+iUeNBR/hFbAAAAAElFTkSuQmCC)"></i>
<i class="p ob" style="left:38.082%;top:79.421%;width:5.342%;height:5.788%;--d:1.6s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACcAAAASCAYAAADYFMcrAAABA0lEQVR42s2W7U2EQBRFDzSAHawdUAKJDRhtwGAJdkAJW4FbAhVs3AaM2wF2oNvA8c+QkA2f2XWZk9wAP3i5w3vMnURlARvgHnjo3A/RAN/AD/AJHFhIMmEuA56Bx6BLOQTtZ5lV+1Sqtf9Lo1ZqNuCBPlONt+Wk7tTNkLk1TPVRnZsrjIsvNVdJ1PpKw35t3lLgjjjZp8A2QmOvwLEdvl1EM1e2P0S7CWfAMez6a3+x9/YhDddf4CUmY30JUa3dyrGEQP2IwVh35s7Dfun8dUO8uKiVM4I/D5k3loXF0IqDCnU7EovlxPtMFT91DNVzCg7oKSyoCfE0q04y47CZhzbfnD9cADBTJuJnZQAAAABJRU5ErkJggg==)"></i>
<i class="p ob" style="left:42.603%;top:82.637%;width:2.329%;height:5.466%;--d:1.68s;--u:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAYAAAA7bUf6AAAAn0lEQVR42qWTWxGDMBBFLzWQOoiUakACUioBB+AAKdQBtYCBw0ebDs3w2CxnJl+ZnMnu3hUg42n27qyCjg+1V5IEADMQSiVrQWIokWwJEo8zSQBGjpnWZXkEiXZLUiL4K+uK4FdWBQRJL0lRPvqbpP6CQJLuws/8jUGQoxcT8MxHXBsfj3tLaEnnkCf0KPZd9uUWiJYtr4C821HSu2Q8Cyl5QuKQgYaZAAAAAElFTkSuQmCC)"></i>
<div id="sg-burst"></div>
<div id="sg-spark"><svg viewBox="-50 -50 100 100"><path d="M0-50C6-14 14-6 50 0 14 6 6 14 0 50-6 14-14 6-50 0-14-6-6-14 0-50Z"/></svg></div>
</div>
</div>

</div>
<script>
(function () {
    var splash = document.getElementById('sg-splash');
    if (!splash) return;
    var seen = false;
    try { seen = sessionStorage.getItem('sg-splash-seen') === '1'; } catch (e) {}
    if (seen || document.documentElement.classList.contains('sg-splash-skip')) {
        splash.style.display = 'none';
        document.documentElement.classList.remove('sg-splash-on');
        return;
    }
    document.documentElement.classList.add('sg-splash-on');
    try { sessionStorage.setItem('sg-splash-seen', '1'); } catch (e) {}
    var stage = splash.querySelector('#sg-stage');
    var timer = null;
    var hidden = false;
    var started = Date.now();
    function play() {
        if (hidden || !stage) return;
        stage.classList.remove('go');
        void stage.offsetWidth;
        stage.classList.add('go');
        timer = setTimeout(play, 9000);
    }
    play();
    function hide() {
        if (hidden) return;
        hidden = true;
        clearTimeout(timer);
        splash.classList.add('is-done');
        document.documentElement.classList.remove('sg-splash-on');
        setTimeout(function () {
            splash.style.display = 'none';
        }, 500);
    }
    function hideWhenReady() {
        var wait = Math.max(400, 4200 - (Date.now() - started));
        setTimeout(hide, wait);
    }
    if (document.readyState === 'complete') {
        hideWhenReady();
    } else {
        window.addEventListener('load', hideWhenReady);
    }
})();
</script>
@endif
