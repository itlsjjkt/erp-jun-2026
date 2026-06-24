
# ERP Shipping WEB APP


## Setup:

All you need is to run these commands:

```bash

git clone https://gitlab.com/cmi-apps-project/cmos.git

cd cmos

composer install # Install backend dependencies

sudo chmod 777 storage/ -R # Chmod Storage

php artisan storage:link # Enable link to storage

cp .env.example .env # Update database credentials configuration

php artisan key:generate # Generate new keys for Laravel

php artisan migrate:fresh --seed # Run migration and seed users and categories for testing

yarn install # or npm i to Install node dependencies(>= node 9.x)

npm run production # To compile assets for prod

```

  
  

## Demo:

- Online demo: Can be found at [demo.citamineral.com/](http://demo.citamineral.com)

- Local demo:

`php artisan serve # Check installation (optional)`

Open browser at [localhost:8000/admin](http://localhost:8000/admin)

  

**Note:**

Username: admin@example.com

Password: 123456
  

***

  

## Included Packages:

#### Laravel (php):

  

*  [Laravel Framework](https://github.com/laravel/laravel/) (5.8.*)

*  [Forms & HTML](https://github.com/laravelcollective/html) : for forms

*  [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) : for debugging

*  [Intervention Image](https://github.com/intervention/image) : image handling and manipulation

  

#### JS plugins:

  

* All ADMINATOR plugins [here](https://github.com/puikinsh/Adminator-admin-dashboard#built-with)

*  [sweetalert2](https://github.com/limonte/sweetalert2)

*  [Axios](https://github.com/mzabriskie/axios)

  
  

## Page size optimization:

- Using [Laravel Mix](http://laravel.com/docs/master/mix), all CSS and JS are in minified to one file each.

- Laradminator leverages browser caching, using `.htaccess` file from [html5-boilerplate](https://github.com/h5bp/html5-boilerplate)

- GZip compression is activated by default(APP_DEBUG=false => only onfile for js, and one file for css).

-  `app.css`: 107 KB with gzip (943 Kb without)

-  `app.js` : 427 KB with gzip (1.4 Mb without)

  

*__Note:__ If you're using Nginx check: [server-configs-nginx](https://github.com/h5bp/server-configs-nginx)*
