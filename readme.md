# WP_Mail - Send Dynamic Templated Emails with WordPress

<p align="center"><img src="https://ideea.co.uk/static/wp_mail.png"></p>

WP_Mail is the most popular, simplest and powerful dynamic email class available for WordPress. The class provides simple methods for attaching files, custom headers and lots of helper functions. The class only sends emails using the WordPress function wp_mail() , this means that all of your existing SMTP settings will continue to work with no additional config or set-up required.

## Introduction: [Medium Post](https://medium.com/@AnthonyBudd/wp-mail-send-templated-emails-with-wordpress-314a71f83db2)

```php
$email = WP_Mail::init()
    ->to('john.doe@gmail.com')
    ->subject('WP_Mail is great!')
    ->template(get_template_directory() .'/emails/demo.php', [
        'name' => 'Anthony Budd',
        'location' => 'London',
        'skills' => [
           'PHP',
           'AWS',
        ] 
    ])
    ->send();
```

email.html
```html
<h3>You have a new contact from enquirey!</h3><br>

<p>
  <strong>Name:</strong><?= $name ?>
</p>

<p>
  <strong>email:</strong>
  <a href="mailto:<?= $email ?>"><?= $email ?></a>
</p>

<p>
  <strong>Skills:</strong><br>
  <ul>
    <?php foreach($skills as $skill): ?>
      <li>
        <?= $skill ?>
      </li>
    <?php endforeach;?>
  </ul>
</p>
```

***

# Installation

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

# Methods


## to(), cc(), bcc()
All of these functions allow you to set an array or string of recipient(s) for your email as shown in the example below.

```php
    $email = WP_Mail::init()
        ->to([
            'johndoe@gmail.com'
            'mikesmith@gmail.com'
        ])
        ->cc('JackTaylor@gmail.com')
```


## subject()
To set the subject field use the subject function. The first argument will be the emails subject.

```php
    $email = WP_Mail::init()
        ->subject('This this the subject')
```

## from()
To set the from header there is a useful helper function.

```php
    $email = WP_Mail::init()
        ->from('John Doe <john.doe@ideea.co.uk>')
```


## attach()
Similar to the to, cc and bcc, methods the attach method can accept a string or array of stings. This strings must be absolute file paths, this method will throw if the file does not exist.

```php
    $email = WP_Mail::init()
        ->attach(ABSPATH .'wp-content/uploads/2017/06/file.pdf')
```


## template($templatePath, $variables = [])
The templet method is for setting the path to the html email template. The second argument is for an asoc array where the keys will correspond to your HTML email’s variables. Variables are optional and are not required for templates that do not have any variables.

```php
    $email = WP_Mail::init()
        ->template(get_template_directory() .'/email.html', [
           'name' => 'Anthony Budd',
           'job'  => 'Developer',
        ])
```


### templateHeader($templatePath, $variables = [])
### templateFooter($templatePath, $variables = [])
Self-explanatory  


If you are sending many emails the beforeTemplate() and afterTemplate() will allow you to append and prepen templated HTML to your emails.
```php
    $email = (new WP_Mail)
        ->beforeTemplate(get_template_directory() .'/email-header.html')
		->afterTemplate(get_template_directory() .'/email-footer.html')
        ->template(get_template_directory() .'/email.html', [
           'name' => 'Anthony Budd',
           'job'  => 'Developer',
        ])
```




## headers()
This method allows you to set additional headers for your email. This can be an array of headers or a single string header.

```php
    $email = WP_Mail::init()
        ->headers("From: John Doe <john.doe@ideea.co.uk>")
```

```php
    $email = WP_Mail::init()
        ->headers([
            "From: John Doe <john.doe@ideea.co.uk>",
            "X-Mailer: PHP/". phpversion(),
            "Reply-To: webmaster@ideea.co.uk",
            "Content-type: text/html; charset=iso-8859-1",
        ])
```


## render()
This method is called by the send() method, the result is given directly to the $message argument of the wp_mail function. This can be used for testing or for displaying what an email will look like for admins.

The render() method is called when you send an email will use a simple bit of regex to find and replace variables using a mustache-esque syntax. Finally the method sends the email using WordPresses built in wp_mail() function.

```php
    $email = (new WP_Mail)
        ->send()
```

