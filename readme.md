# WP_Mail - Send

<p align="center"><img src="https://c1.staticflickr.com/5/4156/34476075652_c809cd37f6_o.png"></p>

## A simple class for sending templated emails using WordPress

### Introduction: [Medium Post](https://medium.com/@AnthonyBudd/wp-mail-send-templated-emails-with-wordpress-314a71f83db2)


```php
$email = (new WP_Mail)
    ->to('anthonybudd94@gmail.com')
    ->subject('test')
    ->setTemplatePath('email.html', [
        'name' => 'Anthony Budd',
        'job' => 'Developer',
    ])
    ->send();
```

email.html
```html
<h1>Hello {{name}},</h1>
<p>You work as a {{job}}.</p>
```

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

### Methods

#### to(), cc(), bcc()
All of these functions allow you to set an array or string of recipient(s) for your email as shown in the example below.

#### subject()
To set the subject field use the subject function. The first argument will be the emails subject.

#### attach()
Similar to the to, cc and bcc, functions the attach method can accept a string or array of stings. This strings must be absolute file paths, this method will throw if the file does not exist.

#### template($templatePath, $variables = [])
The templet method is for setting the path to the html email template. The second argument is for an asoc array where the keys will correspond to your HTML email’s variables. Variables are optional and are not required for templates that do not have any variables.

#### headers()
This method allows you to set additional headers for your email. This can be an array of headers or a single string header.

#### send()
The render() method is called when you send an email will use a simple bit of regex to find and replace variables using a mustache-esque syntax. Finally the method sends the email using WordPresses built in wp_mail() function.
