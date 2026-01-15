#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
多线程端口扫描工具
适配渗透测试岗位技能要求：支持HTTP/HTTPS服务识别
"""
import socket
import threading
from concurrent.futures import ThreadPoolExecutor
import time

class PortScanner:
    def __init__(self, target_ip, start_port=1, end_port=1000, thread_num=50):
        self.target = target_ip
        self.start_port = start_port
        self.end_port = end_port
        self.thread_num = thread_num
        self.open_ports = []

    def scan_port(self, port):
        """扫描单个端口"""
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(0.5)
            result = sock.connect_ex((self.target, port))
            if result == 0:
                # 尝试识别服务类型（HTTP/HTTPS）
                service = self.get_service(port)
                self.open_ports.append({"port": port, "service": service})
                print(f"[+] 端口 {port} 开放 - 服务：{service}")
            sock.close()
        except Exception as e:
            pass

    def get_service(self, port):
        """识别端口对应的服务"""
        try:
            # 测试HTTP服务
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(0.3)
            sock.connect((self.target, port))
            sock.send(b"GET / HTTP/1.1\r\nHost: localhost\r\n\r\n")
            response = sock.recv(1024)
            if "HTTP/1.1" in response.decode():
                return "HTTP"
            # 测试HTTPS服务
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(0.3)
            sock.connect((self.target, port))
            sock.send(b"GET / HTTP/1.1\r\nHost: localhost\r\n\r\n")
            response = sock.recv(1024)
            if "HTTP/1.1" in response.decode():
                return "HTTPS"
        except:
            pass
        return "Unknown"

    def run(self):
        """启动多线程扫描"""
        print(f"[*] 开始扫描目标 {self.target}（端口 {self.start_port}-{self.end_port}）")
        start_time = time.time()
        
        with ThreadPoolExecutor(max_workers=self.thread_num) as executor:
            executor.map(self.scan_port, range(self.start_port, self.end_port + 1))
        
        end_time = time.time()
        print(f"\n[*] 扫描完成，耗时 {round(end_time - start_time, 2)}s")
        print(f"[*] 开放端口列表：")
        for port_info in self.open_ports:
            print(f"  - 端口 {port_info['port']}：{port_info['service']}")

if __name__ == "__main__":
    # 示例：扫描本地HTTP服务（对应实验中的phpStudy环境）
    scanner = PortScanner(target_ip="127.0.0.1", start_port=80, end_port=8080, thread_num=30)
    scanner.run()