# KintaiManager(coachtech 勤怠管理アプリ)

## Dockerビルド
- `$ git clone git@github.com:asamiwatababe/KintaiManager.git`
- `$ docker-compose up -d --build`

## Laravel環境構築(すべての artisan コマンドや composer install は PHPコンテナ内で実行してください。)
- `$ docker-compose exec php bash`
- `$ composer install`
- `$ cp .env.example .env`環境変数を適宜変更
※ 必要に応じて .env ファイルの内容（DB接続など）を自分の環境に合わせて修正してください
- `$ php artisan key:generate`
- `$ php artisan migrate --seed`

## 開発環境
- トップ画面：[http://localhost/]
- 会員登録画面：[http://localhost/register]
- ログイン画面：[http://localhost/login]

## 使用技術（実行環境）
- Laravel 8.75
- PHP 7.4.9
- MySQL 8.0.26
- Docker / Docker Compose
- GitHub
- phpMyAdmin（http://localhost:8080）

## ER図
![ER図](./er-diagram.svg)

## URL
- 開発環境：http://localhost/

## テスト実行

> すべてのコマンドは **PHP コンテナ内**（`docker-compose exec php bash`）で実行してください。  
> ローカル実行の方は「コンテナ内」を読み替えてください。

### 1) テスト用環境の準備（初回のみ）
```bash
# 1) 複製
cp .env .env.testing

# 2) APP_KEY 発行（.env.testing に対して）
php artisan key:generate --env=testing

# 3) マイグレーション + シード（テスト用DB）
php artisan migrate:fresh --seed --env=testing

# 4) テスト実行
php artisan test --env=testing
)

## ダミーデータ
このリポジトリにはダミーデータ Seeder を用意しています。

### 実行手順
```bash
# PHP コンテナ内で実行
php artisan migrate:fresh --seed
# もしくは DemoDataSeeder のみを実行
php artisan db:seed --class=DemoDataSeeder
