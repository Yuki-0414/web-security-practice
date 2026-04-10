#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
混合内容漏洞自动检测与复现
检测HTTPS页面中加载HTTP资源的风险
"""
import requests
from urllib.parse import urlparse
from bs4 import BeautifulSoup

requests.packages.urllib3.disable_warnings()

def check_mixed_content(url):
    result = {
        "url": url,
        "is_https": url.startswith("https"),
        "mixed_resources": [],
        "risk_level": "安全",
        "csp_exists": False
    }

    try:
        resp = requests.get(url, timeout=10, verify=False)
        result["status_code"] = resp.status_code

        if "Content-Security-Policy" in resp.headers:
            result["csp_exists"] = True

        soup = BeautifulSoup(resp.text, "html.parser")
        tags = {
            "script": "src",
            "img": "src",
            "link": "href",
            "iframe": "src"
        }

        for tag, attr in tags.items():
            for elem in soup.find_all(tag):
                val = elem.get(attr, "")
                if val.startswith("http:"):
                    result["mixed_resources"].append({
                        "type": tag,
                        "url": val
                    })

        if len(result["mixed_resources"]) > 0:
            result["risk_level"] = "高危" if not result["csp_exists"] else "中危"

    except Exception as e:
        result["error"] = str(e)

    return result

def print_report(res):
    print("=" * 50)
    print("混合内容漏洞检测报告")
    print("=" * 50)
    print(f"目标URL: {res['url']}")
    print(f"HTTPS页面: {res['is_https']}")
    print(f"CSP头存在: {res['csp_exists']}")
    print(f"风险等级: {res['risk_level']}")
    print(f"混合资源数量: {len(res['mixed_resources'])}")
    for item in res["mixed_resources"]:
        print(f"  - {item['type']}: {item['url']}")

if __name__ == "__main__":
    target = "https://www.bennish.net/mixed-content.html"
    report = check_mixed_content(target)
    print_report(report)