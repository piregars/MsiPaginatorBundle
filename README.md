MsiPaginatorBundle
===
Add to deps:

    [MsiPaginatorBundle]
        git=git://github.com/gnal/MsiPaginatorBundle.git
        target=bundles/Msi/Bundle/PaginatorBundle

Register bundle:

    new Msi\Bundle\PaginatorBundle\MsiPaginatorBundle(),

Register namespace:

    'Msi' => __DIR__.'/../vendor/bundles',
