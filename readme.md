# WP_Mail

<p align="center"><img src="https://c1.staticflickr.com/1/415/31850480513_6cf2b5bdde_b.jpg"></p>

## A simple class for sending templated emails using WordPress

### Introduction: [Medium Post](https://medium.com/@AnthonyBudd/wp-model-6887e1a24d3c)


```php
$email = (new WP_Mail)
    ->to('anthonybudd94@gmail.com')
    ->subject('test')
    ->setTemplatePath('emails/email.html', [
        'name' => 'Anthony Budd',
        'job' => 'Developer',
    ])
    ->send();
```

```html
<!-- emails/email.html -->
<h1>Hello {{name}},</h1>
<p>You work as a {{job}}.</p>
```

***

### In A Nutshell

The send() method renders the template and will fire the built in WordPress function wp_mail().

***

### Installation

Require WP_Mail with composer

```
$ composer require anthonybudd/WP_Mail
```

#### Or

Download the WP_Mail class and require it at the top of your functions.php file.

```php
    require 'src/WP_Mail.php';
```

***

### Setup
You will then need to make a class that extends WP_Model. This class will need the public property $postType and $attributes, an array of strings.

If you need to prefix the model's data in your post_meta table add a public property $prefix. This will be added to the post meta so the attribute 'color' will be saved in the database using the meta_key 'wp_model_color'
```php
Class Product extends WP_Model
{
    public $postType = 'product';
    
    public $prefix = 'wp_model_';

    public $attributes = [
        'color',
        'weight'
    ];
}
```
