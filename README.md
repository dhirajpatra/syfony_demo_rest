Symfony demo with REST API simplification
=========================================
-- Both Web and Api are available for the project.

Install the symfony_demo.sql in root to your DB.
Configure :
app/config/parameters.yml
[as per your requirement can update other routing.yml too]

-- Web
http://symfony_demo/acme/test/default/index
http://symfony_demo/default/index

-- Api
http://symfony_demo/acme/test/api/products/show
Request: {"id":"1"}

http://symfony_demo/acme/test/api/products
Request: {"name":"good api","price":"20.50","description":"good api test"}
