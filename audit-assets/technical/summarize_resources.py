import base64
import json
from collections import Counter
from pathlib import Path
from urllib.parse import urlparse

root = Path(__file__).resolve().parent
assets = json.loads((root / "assets.json").read_text(encoding="utf-8"))
links = json.loads((root / "links-check.json").read_text(encoding="utf-8"))

def numeric(value):
    return str(value).isdigit()


known_lengths = [int(a["content_length"]) for a in assets if numeric(a.get("content_length"))]
asset_summary = {
    "count": len(assets),
    "status_counts": dict(Counter(str(a.get("status", "error")) for a in assets)),
    "type_counts": dict(Counter((a.get("content_type") or "unknown").split(";")[0] for a in assets)),
    "known_content_length_count": len(known_lengths),
    "known_content_length_total_bytes": sum(known_lengths),
    "cache_control_missing_count": sum(not a.get("cache_control") for a in assets),
    "content_encoding_missing_count": sum(not a.get("content_encoding") for a in assets),
    "largest_known": sorted(
        [
            {"url": a["url"], "bytes": int(a["content_length"]), "type": a.get("content_type")}
            for a in assets
            if numeric(a.get("content_length"))
        ],
        key=lambda x: x["bytes"],
        reverse=True,
    )[:15],
    "non_200": [a for a in assets if a.get("status") != 200],
}

link_summary = {
    "count": len(links),
    "scope_counts": dict(Counter(a.get("scope", "unknown") for a in links)),
    "status_counts": dict(Counter(str(a.get("status", "error")) for a in links)),
    "redirected_or_canonicalized": [
        {
            "requested_url": a.get("requested_url"),
            "final_url": a.get("final_url"),
            "status": a.get("status"),
        }
        for a in links
        if a.get("requested_url") != a.get("final_url")
    ],
    "non_200": [a for a in links if a.get("status") != 200 or a.get("error")],
    "external_hosts": sorted({
        urlparse(a.get("requested_url", "")).hostname
        for a in links
        if a.get("scope") == "external"
    }),
}

summary = {"assets": asset_summary, "links": link_summary}

lighthouse_path = root / "lighthouse-mobile.report.json"
if lighthouse_path.exists():
    lighthouse = json.loads(lighthouse_path.read_text(encoding="utf-8"))
    audits = lighthouse["audits"]

    def audit_result(audit_id):
        audit = audits.get(audit_id, {})
        details = audit.get("details", {})
        return {
            "id": audit_id,
            "score": audit.get("score"),
            "numeric_value": audit.get("numericValue"),
            "numeric_unit": audit.get("numericUnit"),
            "display_value": audit.get("displayValue"),
            "title": audit.get("title"),
            "item_count": len(details.get("items", [])) if isinstance(details, dict) else None,
        }

    failures = {}
    for category_id in ("performance", "accessibility", "best-practices", "seo"):
        refs = lighthouse["categories"].get(category_id, {}).get("auditRefs", [])
        failures[category_id] = [
            audit_result(ref["id"])
            for ref in refs
            if audits.get(ref["id"], {}).get("score") not in (None, 1)
            and ref.get("weight", 0) > 0
        ]

    network_items = audits.get("network-requests", {}).get("details", {}).get("items", [])
    lighthouse_summary = {
        "fetch_time": lighthouse.get("fetchTime"),
        "requested_url": lighthouse.get("requestedUrl"),
        "final_url": lighthouse.get("finalDisplayedUrl"),
        "lighthouse_version": lighthouse.get("lighthouseVersion"),
        "user_agent": lighthouse.get("userAgent"),
        "config_settings": lighthouse.get("configSettings"),
        "category_scores": {
            key: round(value.get("score", 0) * 100)
            for key, value in lighthouse.get("categories", {}).items()
        },
        "metrics": {
            key: audit_result(key)
            for key in (
                "first-contentful-paint",
                "largest-contentful-paint",
                "speed-index",
                "total-blocking-time",
                "cumulative-layout-shift",
                "interactive",
                "server-response-time",
                "total-byte-weight",
                "network-requests",
                "dom-size",
            )
        },
        "opportunities": {
            key: audit_result(key)
            for key in (
                "render-blocking-resources",
                "unused-css-rules",
                "unused-javascript",
                "uses-long-cache-ttl",
                "modern-image-formats",
                "uses-responsive-images",
                "offscreen-images",
                "unminified-css",
                "unminified-javascript",
                "uses-text-compression",
            )
        },
        "weighted_failures": failures,
        "network_summary": {
            "request_count": len(network_items),
            "transfer_size_bytes": sum(item.get("transferSize", 0) or 0 for item in network_items),
            "resource_size_bytes": sum(item.get("resourceSize", 0) or 0 for item in network_items),
            "status_counts": dict(Counter(str(item.get("statusCode", "unknown")) for item in network_items)),
            "failed_requests": [
                {
                    "url": item.get("url"),
                    "status": item.get("statusCode"),
                    "resource_type": item.get("resourceType"),
                }
                for item in network_items
                if (item.get("statusCode") or 0) >= 400
            ],
        },
        "console_errors": audits.get("errors-in-console", {}).get("details", {}).get("items", []),
        "inspector_issues": audits.get("inspector-issues", {}).get("details", {}).get("items", []),
    }
    (root / "lighthouse-summary.json").write_text(
        json.dumps(lighthouse_summary, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    screenshot_data = audits.get("final-screenshot", {}).get("details", {}).get("data", "")
    if screenshot_data.startswith("data:image/") and "," in screenshot_data:
        (root / "lighthouse-final-screenshot.png").write_bytes(
            base64.b64decode(screenshot_data.split(",", 1)[1])
        )
    summary["lighthouse"] = lighthouse_summary

desktop_path = root / "lighthouse-desktop.json"
if desktop_path.exists():
    desktop = json.loads(desktop_path.read_text(encoding="utf-8"))
    desktop_audits = desktop["audits"]
    desktop_summary = {
        "fetch_time": desktop.get("fetchTime"),
        "lighthouse_version": desktop.get("lighthouseVersion"),
        "config_settings": desktop.get("configSettings"),
        "category_scores": {
            key: round(value.get("score", 0) * 100)
            for key, value in desktop.get("categories", {}).items()
        },
        "metrics": {
            key: {
                "numeric_value": desktop_audits.get(key, {}).get("numericValue"),
                "numeric_unit": desktop_audits.get(key, {}).get("numericUnit"),
                "display_value": desktop_audits.get(key, {}).get("displayValue"),
            }
            for key in (
                "first-contentful-paint",
                "largest-contentful-paint",
                "speed-index",
                "total-blocking-time",
                "cumulative-layout-shift",
                "interactive",
                "server-response-time",
                "total-byte-weight",
            )
        },
    }
    (root / "lighthouse-desktop-summary.json").write_text(
        json.dumps(desktop_summary, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    summary["lighthouse_desktop"] = desktop_summary

(root / "resource-summary.json").write_text(
    json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8"
)
print(json.dumps(summary, ensure_ascii=False, indent=2))
