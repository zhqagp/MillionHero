# 百万英雄、冲顶大会、芝士超人答题助手
----------------------------------------------------------------------------------------------

## 我的使用环境是Mac Pro+PHP7.1

本项目参考 https://github.com/wuditken/MillionHeroes ，使用adb截图，通过百度OCR识别接口返回具体问题内容，然后通过百度接口获取前三个结果。

## 把问题区域裁剪出来后用百度的ocr识别出文本，然后调用百度搜索（可以同时打开浏览器）

## 整个程序运行完估计2-3秒左右，还可以有时间答题（---）

## 使用教程

1.安装ADB 驱动，可以到[这里下载](https://adb.clockworkmod.com/)<br />
   安装 ADB 后，请在环境变量里将 adb 的安装路径保存到 PATH 变量里，确保 adb 命令可以被识别到

2.在hero.php里填写自己百度ocr的APP_ID/API_KEY/SECRET_KEY</br>
百度ocr：http://ai.baidu.com/tech/ocr/general

3.连接手机<br>运行php hero.php
（只支持安卓手机）




