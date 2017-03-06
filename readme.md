
### 开发 pgPhone扩展

### 用法

```
composer require chenze/pangu-phone
```

或者在你的 `composer.json` 的 require 部分中添加:
```json
 "chenze/pangu-phone": "~v1.0"
```

下载完毕之后,直接配置 `app/config.php` 的 `providers`:

```php
\Aze\panguPhone\PanguPhoneServiceProvider::class,

```