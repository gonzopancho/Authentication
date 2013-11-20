#!/bin/bash
SUDO=sudo

# root ユーザーの場合 sudo コマンドを抜かす
if [ $EUID -eq 0 ]; then
	SUDO=""
fi

# 最低限必要なパッケージをインストール
${SUDO} apt-get -y install apache2 php5 php5-{json,mysql,curl}
${SUDO} apt-get -y install mysql-server
${SUDO} apt-get -y install curl subversion

