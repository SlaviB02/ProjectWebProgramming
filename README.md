# ProjectWebProgramming
App for Scientific Articles

**Admin**
username: admin 
pass: admin123

# How to run project

1. Run php -S localhost:8000 in the folder.
2. Go to localhost:8000/install.php to run the installation script.
3. Use the site.

#API

1.GET с curl
curl -X GET http://localhost:8000/api/records.php -H "Authorization: my_super_secret_token_123456"
2.POST с curl
curl -X POST http://localhost:8000/api/records.php ^
-H "Authorization: my_super_secret_token_123456" ^
-H "Content-Type: application/json" ^
-d "{\"title\":\"Test Article\",\"author\":\"Ivan Ivanov\",\"publication\":\"Tech Journal\"}"
