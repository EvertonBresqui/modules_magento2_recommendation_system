## Magento 2

### Instalar o módulo System no qual realiza as tarefas de core ou seja comunicação e funções dinâmicas e o modulo Als.

1. Copiar os arquivos para a pasta do magento
2. Rodar os seguintes comandos:

> php bin/magento cache:clean

> php bin/magento cache:flush

> php bin/magento setup:upgrade

> php bin/magento setup:di:compile

> php bin/magento cache:flush

> php bin/magento setup:static-content:deploy -f

> php bin/magento setup:static-content:deploy -f pt_BR

3. Após os modulos instalados, configure primeiramente o System, após as próximas opções e rode os seguintes comandos::

> php bin/magento cache:clean

> php bin/magento cache:flush

> php bin/magento cron:install

> php bin/magento cron:run
