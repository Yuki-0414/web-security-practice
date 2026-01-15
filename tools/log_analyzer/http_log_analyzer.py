#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""HTTP访问日志异常检测（适配安全运维技能）"""
import pandas as pd
import re
from collections import Counter

def analyze_http_log(log_path):
    """分析HTTP访问日志，检测异常行为"""
    # 读取日志文件（格式：IP - - [时间] "请求" 状态码 大小）
    log_pattern = r'(\d+\.\d+\.\d+\.\d+) - - \[.*\] "(\w+) .*" (\d+) (\d+)'
    logs = []
    
    with open(log_path, "r", encoding="utf-8") as f:
        for line in f:
            match = re.search(log_pattern, line)
            if match:
                ip = match.group(1)
                method = match.group(2)
                status_code = match.group(3)
                size = match.group(4)
                logs.append([ip, method, status_code, size])
    
    # 转换为DataFrame分析
    df = pd.DataFrame(logs, columns=["IP", "Method", "Status_Code", "Size"])
    print(f"日志总条数：{len(df)}")
    
    # 1. 统计访问最多的IP（可能是暴力破解）
    top_ips = Counter(df["IP"]).most_common(3)
    print("\n访问次数最多的前3个IP：")
    for ip, count in top_ips:
        print(f"  {ip}：{count}次")
    
    # 2. 统计异常状态码（404：路径扫描，403：权限绕过，500：服务器错误）
    abnormal_codes = df[df["Status_Code"].isin(["404", "403", "500"])]
    print(f"\n异常状态码统计：")
    print(abnormal_codes["Status_Code"].value_counts())
    
    # 3. 统计请求方法（POST过多可能是表单爆破）
    method_count = df["Method"].value_counts()
    print(f"\n请求方法统计：{dict(method_count)}")

if __name__ == "__main__":
    # 示例：创建测试日志文件（实际使用时替换为真实日志路径）
    test_log = """192.168.1.1 - - [10/Sep/2025:10:00:00 +0800] "GET / HTTP/1.1" 200 1024
192.168.1.2 - - [10/Sep/2025:10:01:00 +0800] "POST /login HTTP/1.1" 404 512
192.168.1.2 - - [10/Sep/2025:10:01:01 +0800] "POST /login HTTP/1.1" 404 512
192.168.1.2 - - [10/Sep/2025:10:01:02 +0800] "POST /login HTTP/1.1" 404 512
192.168.1.3 - - [10/Sep/2025:10:02:00 +0800] "GET /admin HTTP/1.1" 403 256"""
    
    with open("test_http.log", "w", encoding="utf-8") as f:
        f.write(test_log)
    
    analyze_http_log("test_http.log")