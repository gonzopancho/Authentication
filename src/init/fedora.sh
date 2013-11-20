#!/bin/bash
SUDO=sudo

# root ユーザーの場合 sudo コマンドを抜かす
if [ $EUID -eq 0 ]; then
	SUDO=""
fi

# 最低限必要なパッケージをインストール
if ! rpm -q php-{mbstring,mysql,pdo,gd} >/dev/null 2>&1; then
	${SUDO} yum -y install httpd php-{mbstring,mysql,pdo,gd}
	${SUDO} yum -y install php mysql mysql-server
fi

# MySQLデーモンを立ち上げる
${SUDO} service mysqld restart

# vim: set nu ts=2 autoindent : #

