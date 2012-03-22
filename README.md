Installation
-----------------

Update deps file

```
[StfalconPaymentBundle]
    git=https://github.com/stfalcon/PaymentBundle.git
    target=bundles/Stfalcon/Bundle/PaymentBundle
```

Update autoload.php

```php
<?php
$loader->registerNamespaces(array(
   ...
    'Stfalcon'         => __DIR__.'/../vendor/bundles'
    ...
));
```

Update AppKernel.php

```php
<?php
        $bundles = array(
            ....
            new Stfalcon\Bundle\PaymentBundle\StfalconPaymentBundle()
            ....
        );
```

Update Configuration config.yml

```yml
stfalcon_payment:
    interkassa:
        shop_id:
        secret_key:
```

Update routing.yml

```yml
StfalconPaymentBundle:
    resource: "@StfalconPaymentBundle/Resources/config/routing/application.xml"
    prefix:   /payment
```
