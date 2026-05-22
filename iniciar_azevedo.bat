@echo off
title Azevedo Patrimonial - Servidor
cd /d "C:\Projetos\gestao-imoveis"
echo Iniciando Azevedo Patrimonial...
echo Gerando alertas automaticos...
php artisan sistema:gerar-alertas
echo Alertas gerados! Iniciando servidor...
start "" "http://127.0.0.1:8000"
php artisan serve
pause