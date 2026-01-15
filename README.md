# web-security-practice

# Web安全实践合集
[![Python Version](https://img.shields.io/badge/python-3.8+-blue.svg)]()
[![License](https://img.shields.io/badge/license-MIT-green.svg)]()

## 项目介绍
基于Web编程与网络安全实验（HTTP/HTTPS协议分析、Web安全测试）开发的实操项目，包含**协议分析、安全工具开发、漏洞复现与修复**三大核心模块，适配Web安全工程师/安全运维岗位技能要求。

## 核心技能栈
- 网络协议：HTTP/1.1、HTTPS（SSL/TLS）、TCP/IP
- 安全工具：Wireshark、BurpSuite、Python爬虫与抓包、MySQL
- 开发语言：Python3（requests、scapy、pandas）、Java 、C
- Web安全：混合内容漏洞、明文传输风险、端口扫描、日志分析

## 模块详情
### 1. HTTP协议分析（http_analysis/）
- 基于Wireshark/BurpSuite实现HTTP/HTTPS数据包捕获与解析
- 自动化对比HTTP明文传输与HTTPS加密传输的安全性差异
- 复现混合内容（HTTP资源嵌入HTTPS页面）的安全风险

### 2. Web漏洞实践（web_vulnerability/）
- 混合内容漏洞：复现"并非完全安全"场景，提供修复方案
- HTTP不安全协议：演示明文传输导致的数据泄露风险
- 包含漏洞页面与修复后页面的对比演示

### 3. 安全工具开发（tools/）
- 端口扫描工具：多线程扫描目标端口，支持HTTP/HTTPS服务识别
- HTTP日志分析：检测异常访问、暴力破解等风险行为

## 快速开始
```bash
# 克隆仓库
git clone https://github.com/你的账号/web-security-practice.git
cd web-security-practice

# 安装依赖
pip install -r requirements.txt

# 运行示例：HTTP/HTTPS协议对比测试
python http_analysis/protocol_compare/http_https_diff.py

# 运行示例：混合内容风险测试
python http_analysis/protocol_compare/mixed_content_test.py

