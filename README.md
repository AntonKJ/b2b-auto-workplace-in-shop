Myexample B2B auto
===============================

#### Production: https://ml.myexample.ru/

## Настройка проекта:

Установить актуальные версии `VirtualBox` и `vagrant`

Установить `php 7.2`

Клонировать актуальную ветку репозитория и переключится на ветку `master`
(предварительно добавить ssh-ключ в профиль на GitLab)

Далее в конфигурацию `/b2b/vagrant/config/vagrant-local.yml` добавить GitHub token (сгенерировать 
тут `https://github.com/settings/tokens`) и увеличить память до 4096

Далее в папке `/b2b/` выполнить `./init`

#### Миграции:

```
vagrant ssh
cd /app
./yii migrate
```

Далее в конфигурации `/b2b/console/config/main-local.php` добавить строку `‘indexPath’ => ‘/sphinxsearch’` 
 перед аргументом `tsvPipeCommand`
 
После необходимо скачать актуальную версию `composer.phar` и скопировать его в папку `/b2b/`

Далее в директории `/b2b/` выполнить `php composer.phar install`

После в директории `/b2b/` выполнить `vagrant up`, возможно несколько раз для установки всех зависимостей


Если после выполнения команды `vagrant up` есть ошибки связанные с `sphinxsearch`, выполнить следующие команды:
```
vagrant ssh
sudo mkdir /sphinxsearch
sudo chown sphinxsearch:sphinxsearch -R /sphinxsearch/
sudo chmod -R 777 sphinxsearch/
chmod -R 777 /app/console/runtime/
then ctrl+D to exit from container

vagrant halt
vagrant up
```

#### 1С

На тестовом сервере - `185.60.133.29`

#### Запуск проекта
```
vagrant up - для запуска проекта
vagrant halt - для остановки проекта
```

```
ssh -L 0.0.0.0:22000:109.73.36.118:80 operator@185.60.133.29 -p 65022
```

Урл для **wsdl**

`http://WebObmen:nembObeW@185.60.133.29:22000/ut/ws/ExchangeOfSite/ExchangeOfSite.1cws?wsdl`
