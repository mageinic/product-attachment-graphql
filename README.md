# Product Attachment GraphQL

**Product Attachment GraphQL is a part of MageINIC Product Attachment extension that adds GraphQL features.** This extension extends Product Attachment definitions.

## 1. How to install

Run the following command in Magento 2 root folder:

```
composer require mageinic/product-attachment-graph-ql

php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```

**Note:**
Magento 2 Product Attachment GraphQL requires installing [MageINIC Product Attachment](https://github.com/mageinic/product-attachment) in your Magento installation.

**Or Install via composer [Recommend]**
```
composer require mageinic/product-attachment
```

## 2. How to use

- To view the queries that the **MageINIC Product Attachment GraphQL** extension supports, you can check `Product Attachment GraphQl User Guide.pdf` Or run `ProductAttach Graphql.postman_collection.json` in Postman.

## 3. Get Support

- Feel free to [contact us](https://www.mageinic.com/contact.html) if you have any further questions.
- Like this project, Give us a **Star**
