#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
HTTP与HTTPS协议差异自动化测试
基于Web编程实验2的HTTP/HTTPS对比需求开发
功能：测试不同协议的传输安全性、响应速度、内容完整性
"""
import requests
import time
from requests.packages.urllib3.exceptions import InsecureRequestWarning

# 忽略HTTPS证书警告
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

# 测试目标（涵盖实验中的安全/不安全场景）
TEST_TARGETS = {
    "HTTP不安全站点": "http://202.192.33.200:5000/",
    "HTTPS安全站点": "https://www.baidu.com/",
    "混合内容站点": "https://www.bennish.net/mixed-content.html"
}

def test_protocol(target_url, protocol_type):
    """测试协议传输特性"""
    result = {
        "url": target_url,
        "protocol": protocol_type,
        "status_code": None,
        "response_time": None,
        "is_encrypted": protocol_type == "HTTPS",
        "security_warning": False
    }
    
    try:
        # 记录响应时间
        start_time = time.time()
        if protocol_type == "HTTP":
            response = requests.get(target_url, timeout=10)
        else:
            # HTTPS测试（验证证书有效性）
            response = requests.get(target_url, timeout=10, verify=False)
            # 检测混合内容（通过响应头判断）
            if "Content-Security-Policy" not in response.headers:
                result["security_warning"] = True
        
        result["status_code"] = response.status_code
        result["response_time"] = round(time.time() - start_time, 3)
        result["content_length"] = len(response.content)
    
    except Exception as e:
        result["error"] = str(e)
    
    return result

def print_report(results):
    """打印测试报告"""
    print("="*50)
    print("HTTP/HTTPS协议对比测试报告")
    print("="*50)
    for res in results:
        print(f"\n【{res['url']}】")
        print(f"协议类型：{res['protocol']}")
        print(f"状态码：{res['status_code'] if res['status_code'] else '访问失败'}")
        print(f"响应时间：{res['response_time']}s" if res['response_time'] else "响应超时")
        print(f"传输加密：{'是' if res['is_encrypted'] else '否（明文传输，存在泄露风险）'}")
        if res['security_warning']:
            print(f"安全警告：存在混合内容/缺少CSP头,可能被注入恶意代码")
        if "error" in res:
            print(f"错误信息：{res['error']}")

if __name__ == "__main__":
    test_results = []
    for name, url in TEST_TARGETS.items():
        protocol = "HTTPS" if url.startswith("https") else "HTTP"
        test_results.append(test_protocol(url, protocol))
    
    # 生成测试报告
    print_report(test_results)
    # 保存结果到文件（便于分析）
    with open("http_https_test_report.txt", "w", encoding="utf-8") as f:
        f.write(str(test_results))
    print("\n测试报告已保存到 http_https_test_report.txt")