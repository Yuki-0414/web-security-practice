#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""BurpSuite拦截与修改示例（对应实验1工具使用）"""
import requests

# 配置BurpSuite代理（需在BurpSuite中开启代理，默认端口8080）
proxies = {
    'http': 'http://127.0.0.1:8080',
    'https': 'http://127.0.0.1:8080'
}

def burp_capture_demo(url):
    """演示通过BurpSuite拦截HTTP请求"""
    try:
        # 发送请求（会被BurpSuite拦截）
        response = requests.get(url, proxies=proxies, verify=False)
        print(f"响应状态码：{response.status_code}")
        print(f"响应头：{dict(response.headers)[:10]}")  # 只显示前10个头部
    except Exception as e:
        print(f"请确保BurpSuite已开启代理，错误信息：{str(e)}")

if __name__ == "__main__":
    # 测试目标（实验中的测试站点）
    burp_capture_demo("http://202.192.33.200:5000/")