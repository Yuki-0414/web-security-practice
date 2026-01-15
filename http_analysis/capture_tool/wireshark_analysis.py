#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Wireshark抓包结果解析脚本（对应实验1抓包分析）"""
import pandas as pd

def parse_wireshark_csv(csv_path):
    """解析Wireshark导出的CSV格式抓包文件"""
    # 读取CSV文件（Wireshark需导出为CSV格式）
    df = pd.read_csv(csv_path)
    # 筛选HTTP/HTTPS数据包
    http_packets = df[df['Protocol'].isin(['HTTP', 'HTTPS'])]
    https_packets = df[df['Protocol'] == 'HTTPS']
    
    print(f"总数据包数：{len(df)}")
    print(f"HTTP数据包数：{len(http_packets)}")
    print(f"HTTPS数据包数：{len(https_packets)}")
    
    # 输出前5条HTTP请求详情
    if len(http_packets) > 0:
        print("\n前5条HTTP请求详情：")
        for idx, row in http_packets.head().iterrows():
            print(f"时间：{row['Time']} | 源IP：{row['Source']} | 目的IP：{row['Destination']} | 信息：{row['Info'][:50]}")
    
    return df

if __name__ == "__main__":
    # 示例：解析Wireshark导出的CSV文件（需手动导出实验抓包结果）
    try:
        parse_wireshark_csv("wireshark_capture.csv")
    except FileNotFoundError:
        print("请将Wireshark抓包结果导出为CSV格式，并重命名为wireshark_capture.csv放在当前目录")