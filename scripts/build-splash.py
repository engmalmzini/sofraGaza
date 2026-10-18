from pathlib import Path
import re

src = Path(r"c:\Users\MSI GF63\Downloads\سفرة - أنيميشن اللوجو (حرف بحرف).html").read_text(encoding="utf-8")
style = re.search(r"<style>(.*?)</style>", src, re.S).group(1)
style = re.sub(r"#btns button\{[^}]+\}", "", style)
style = re.sub(r"#btns\{[^}]+\}", "", style)
style = re.sub(r"html,body\{[^}]+\}", "", style)
style = re.sub(r"body\{[^}]+\}", "", style)
style = re.sub(r":root\{[^}]+\}", "", style)
style = re.sub(r"#stage\.brand\{[^}]+\}", "", style)
style = re.sub(r"\.brand\{[^}]+\}", "", style)
style = re.sub(r"#stage\{[^}]+\}", "", style)
style = re.sub(r"#logo\{[^}]+\}", "", style)
style = re.sub(r"#stage\s*", "", style)
style = style.replace(".go #spark", "#sg-splash .go #sg-spark")
style = style.replace(".go #burst", "#sg-splash .go #sg-burst")
style = style.replace(".p{", "#sg-splash .p{")
style = style.replace(".go ", "#sg-splash .go ")
style = style.replace("#spark", "#sg-splash #sg-spark")
style = style.replace("#burst", "#sg-splash #sg-burst")
style = style.replace("#sg-splash #sg-splash", "#sg-splash")

html = src.split('<div id="stage"', 1)[1]
html = html.split('<div id="btns">', 1)[0]
html = '<div id="sg-stage" class="go">' + html.split('>', 1)[1]
html = html.replace('<div id="logo">', '<div id="sg-logo">')
html = html.replace('id="burst"', 'id="sg-burst"')
html = html.replace('id="spark"', 'id="sg-spark"')
html = html.rstrip() + "\n</div>\n"

out = Path(__file__).resolve().parents[1] / "resources" / "views" / "partials" / "splash.blade.php"
out.write_text(
    f"""{{{{-- Sofra letter-by-letter splash. Brand colors only. Loops until the page is ready. --}}}}
<style>
#sg-splash {{
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
}}
#sg-splash.is-done {{
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}}
html.sg-splash-on,
html.sg-splash-on body {{
    overflow: hidden;
}}
#sg-splash #sg-stage {{
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #d75600;
}}
#sg-splash #sg-logo {{
    position: absolute;
    left: 50%;
    top: 50%;
    width: min(86vw, 260px);
    aspect-ratio: 730 / 311;
    transform: translate(-50%, -50%);
}}
@media (min-width: 380px) {{
    #sg-splash #sg-logo {{ width: min(82vw, 320px); }}
}}
@media (min-width: 480px) {{
    #sg-splash #sg-logo {{ width: min(72vw, 380px); }}
}}
@media (min-width: 768px) {{
    #sg-splash #sg-logo {{ width: min(54vw, 460px); }}
}}
@media (min-width: 1024px) {{
    #sg-splash #sg-logo {{ width: min(40vw, 540px); }}
}}
@media (min-width: 1440px) {{
    #sg-splash #sg-logo {{ width: min(34vw, 640px); }}
}}
{style}
#sg-splash #sg-stage, #sg-splash .p, #sg-splash #sg-spark svg {{
    --bg: #d75600;
    --fg: #faf6ee;
}}
#sg-splash .p {{
    background: #faf6ee;
}}
#sg-splash #sg-spark svg {{
    fill: #faf6ee;
}}
</style>
<div id="sg-splash" role="status" aria-label="جاري تحميل سفرة غزة">
{html}
</div>
<script>
(function () {{
    var splash = document.getElementById('sg-splash');
    if (!splash) return;
    document.documentElement.classList.add('sg-splash-on');
    var stage = splash.querySelector('#sg-stage');
    var timer = null;
    var hidden = false;
    var started = Date.now();
    function play() {{
        if (hidden || !stage) return;
        stage.classList.remove('go');
        void stage.offsetWidth;
        stage.classList.add('go');
        timer = setTimeout(play, 9000);
    }}
    play();
    function hide() {{
        if (hidden) return;
        hidden = true;
        clearTimeout(timer);
        splash.classList.add('is-done');
        document.documentElement.classList.remove('sg-splash-on');
        setTimeout(function () {{
            splash.style.display = 'none';
        }}, 500);
    }}
    function hideWhenReady() {{
        var wait = Math.max(400, 4200 - (Date.now() - started));
        setTimeout(hide, wait);
    }}
    if (document.readyState === 'complete') {{
        hideWhenReady();
    }} else {{
        window.addEventListener('load', hideWhenReady);
    }}
}})();
</script>
""",
    encoding="utf-8",
)
print("wrote", out, "bytes", out.stat().st_size)
