# gw2-database

[![version][packagist-badge]][packagist]
[![license][license-badge]][license]
[![Travis CI][travis-badge]][travis]
[![Gitter][gitter-badge]][gitter]

[packagist-badge]: https://img.shields.io/packagist/v/codemasher/gw2-database.svg?style=flat-square
[packagist]: https://packagist.org/packages/codemasher/gw2-database
[license-badge]: https://img.shields.io/packagist/l/codemasher/gw2-database.svg?style=flat-square
[license]: https://github.com/codemasher/gw2-database/blob/master/LICENSE
[travis-badge]: https://img.shields.io/travis/codemasher/gw2-database.svg?style=flat-square
[travis]: https://travis-ci.org/codemasher/gw2-database
[gitter-badge]: https://img.shields.io/gitter/room/nwjs/nw.js.svg?style=flat-square
[gitter]: https://gitter.im/chillerlan/gw2hero.es

## Requirements
- **PHP 7.2+**
- **MySQL** or **MariaDB**

## Installation
**requires [composer](https://getcomposer.org)**

*composer.json* (note: replace `dev-master` with a version boundary)
```json
{
	"require": {
		"php": ">=7.2.0",
		"codemasher/gw2-database": "dev-master"
	}
}
```

#### Manual installation
Download the desired version of the package from [master](https://github.com/codemasher/gw2-database/archive/master.zip) or
[release](https://github.com/codemasher/gw2-database/releases) and extract the contents to your project folder.  After that:
- run `composer install` to install the required dependencies and generate `/vendor/autoload.php`.
- if you use a custom autoloader, point the namespace `chillerlan\GW2DB` to the folder `src` of the package

Profit!

##Disclaimer!
I don't take responsibility for molten CPUs, smashed keyboards, broken screens etc. Use at your own risk! ;)
