# Laravel 8 Restful Example

_Bu repo PHP 7.4 üzerine Laravel 8 ile inşa edilmiş örnek bir çalışmadır._ 

Kullanıcı doğrulama için JWT token ( [firebase/php-jwt](https://github.com/firebase/php-jwt) ) kullanır.

```
docker-compose up
```

- [Postman Collection](./Laravel%20RestAPI.postman_collection.json)
- Docker
  - Apache (Port : **8000**)
  - PHP 8.1.0
  - MySQL ("**root**" kullanıcısnın şifresi "**123456**")
  - Adminer (Port : **8080**) (MySQL için hafif web yönetim paneli)
- [Veritabanı Şeması](./db-schema.sql)

# Örnek HTTP İstek ve Yanıtlar

## Kullanıcı İşlemleri

#### Kullanıcı Giriş

- `username` olarak kayıt olurken tanımladığınız mail adresi verilebilir.

```
POST /api/login

{
    "username": "newuser",
    "password": "123456",
}
```

#### Yeni Kullanıcı Kayıdı

```
POST /api/register

{
    "name": "Yeni",
    "surname": "Kullanıcı",
    "username": "newuser",
    "password": "123456",
    "email": "example@example.com"
}
```

#### Kullanıcı Silme/Yoketme

- Kullanıcıyı silmek için tüm siparişleri tamamlanmamış olmalıdır.

```
DELETE /api/destroy
Authorization: Bearer aa435...

```

## Sipariş İşlemleri

#### Bütün Siparişleri Listeleme

```
GET /api/order/
Authorization: Bearer aa435...

```

#### Sipariş Detayını Görüntüleme

```
GET /api/order/show/1
Authorization: Bearer aa435...

```

#### Siparişi Güncelleme

- `address` belirtilmezse şiparişin adres bilgisi güncellenmez/değiştirilmez.
- Sipariş durumu "kargolanmış" ya da "teslim edilmiş" ise güncelleme işlemi yapılmaz.
- Sipariş durumu "onaylanmış/hazırlanıyor" ise yeniden "onay bekliyor" olarak değiştirilir.
- Bir ya da daha fazla ürün, yeterli miktarda (stok) yoksa cevapta `errors` dizisinde mesaj olarak bildirilir. Siparişin (ilgili ürün için) önceki halindeki miktarı korunur.

```
PUT /api/order/update/1
Authorization: Bearer aa435...

{
    "address": "Atatürk Mah. 999 Sokak. No:1 İstanbul",
    "products": [
        {
            "id":1,
            "quantity":1
        },
        {
            "id":2,
            "quantity":6
        },
        {
            "id":3,
            "quantity":1
        }
    ]
}
```

#### Siparişi Silme

- Sipariş "kargolanmış" olmamalıdır.

```
DELETE /api/order/delete/1
Authorization: Bearer aa435...
```

#### Yeni Sipariş Oluşturma

```
POST /api/order/create
Authorization: Bearer aa435...

{
    "address": "Atatürk Mah. 999 Sokak. No:1 İstanbul",
    "products": [
        {
            "id":1,
            "quantity":2
        },
        {
            "id":2,
            "quantity":5
        }
    ]
}
```
