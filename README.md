# Crypto Scam Mockup Webpage
This repository contains code for a mockup cryptocurrency scamming website, as [showcased in an OWASP Zap demonstration on Hak5](https://youtube.com/Hak5).

<p align="center">
  <img src="images/mockup.png" width="700px">
  <br>
  <b>Mockup CryptoScam Site.</b>
  <br>
</p>

### About
This application demonstrates the weakness of improper user input validation, since the "private wallet keys" are only validated on the client side via `index.html`.  
By creating a raw `POST` request to `process.php`, we can bypass the validation and spam a server with fake cryptocurrency private keys, as well as the [entire bee movie script]().

### Setup Guide
To follow this setup guide you'll need a Linux computer (we used a Raspberry Pi).

First start by installing the Nginx webserver, and PHP language.
```
sudo apt install nginx 
sudo apt install php-fpm php-mysql
```
Next, update your Nginx configuration file with  
```
sudo nano /etc/nginx/sites-available/default
```

Uppdate the default website block by changing the following parameters:
``` 
server {
	listen 80 default_server;
	listen [::]:80 default_server;

    # website file location
	root /var/www/html/Crypto-Scam-Mockup/;
    
	index index.html index.htm index.nginx-debian.html index.php;

	server_name _;

	location / {
		try_files $uri $uri/ =404;
	}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
	}

	location ~ /\.ht {
		deny all;
	}
}
```
Next, reload Nginx with  
```
sudo service nginx reload
```

Finally, clone this GitHub repository under the `/var/www/html` folder with:
```
cd /var/www/html && git clone https://github.com/AlexLynd/Crypto-Scam-Mockup
```

You should now be able to view the website in a browser at `localhost` from your device, or from its ip address!  You can find the ip address by running `ifconfig`.

If the server is unable to write to the secretwallets.csv file, you may need to give it write permissions with 
```
sudo chmod 777 secretwallets.csv
```

### Walkthrough
The `index.html` mockup page allows a user to input their "Ethereum private key" in a textbox like you can see from the mockup picture above.  
However, in order to validate if the user input is a correctly formatted private key, we use the following Javascript code block.
```
var walletVal = document.getElementById("wallet").value;
var re = new RegExp("^0x[a-fA-F0-9]{64}$");

    if (re.test(walletVal)) {
        // execute code if successful
    }
```
Using the following [regular expression](), we can create a simple filter for user input that matches a 64 bit hex address.
```
^0x[a-fA-F0-9]{64}$
```
Once the user inputs the private key, we can construct a `POST` request that sends a JSON object `{"eth":"0x69696969"}` with the wallet address to `process.php`.
```
const xhttp = new XMLHttpRequest();
xhttp.open("POST", "process.php");
xhttp.setRequestHeader("Content-type", "application/json");
xhttp.send("{\"eth\":\"" + walletVal + "\"}");
```
Since our simple backend processor assumes that the input is properly formatted, it logs all data it recieves to `secretwallets.csv`.
```
header("Content-Type: application/json"); 
  $data = json_decode(file_get_contents("php://input"));

  if ($data->eth) {  // if ethereum wallet key
      $wallet = fopen("secretwallets.csv", "a+") or die("Can't open file, check permissions");
      foreach ($data as $key => $value) {
        fwrite($wallet, $value."\n");
      }
  }
  ```
This means that any raw API requests made directly to `x.x.x.x/process.php` will be logged, since the site doesn't have proper input validation, and since the private keys aren't actually being checked against anything!  
We sometimes encounter sites with poor input sanitization or client-side validation which makes it easy to spam a server with bogus data.  Check out our video below to see how we spammed a real crypto scamming site using OWASP Zap! 