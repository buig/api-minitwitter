# 🐦 MiniTwitter API (Symfony 6.4 + API Platform)

API RESTful desarrollada en Symfony 6.4 con API Platform.  
Permite interactuar con el sistema MiniTwitter: usuarios, posts, likes, follows, hashtags, mensajes y más.

---

## ⚙️ Instalación

```bash
git clone https://github.com/buig/api-minitwitter.git

cd api-minitwitter

# Opcional dependiendo de la configuración de redes de cada uno
(docker network create edu-shared)
(docker network create apps-shared)

# Construcción del contenedor
docker compose up -d --build

# Conexión al contenedor
docker compose exec web bash

# Instalación de dependencias del proyecto
composer install

# Creamos el fichero de configuración
cp .env .env.local

# En el fichero .env.local configuramos la url de la conexión a la base de datos dependiendo del entorno de cada uno.
# Dejo ejemplo de cual sería mi caso
DATABASE_URL="mysql://user:dbpass@db_cont_name:3306/mini_twitter?serverVersion=8.0&charset=utf8mb4"

# Creación de la base de datos
php bin/console doctrine:migrations:migrate

# Carga de datos de prueba
php bin/console doctrine:fixtures:load --no-interaction
```

## ⚙️ Base URL

```
http://localhost:8082/api
```

---

## 🧍‍♂️ Users

| Método 	 | Endpoint                              	 | Descripción                                                         	 |
|----------|-----------------------------------------|-----------------------------------------------------------------------|
| GET    	 | /users                                	 | Lista todos los usuarios                                            	 |
| GET    	 | /users/{id}                           	 | Obtiene los datos de un usuario                                     	 |
| POST   	 | /users/{id}/follow?userId={idUsuario} 	 | El usuario indicado sigue / deja de seguir al usuario {id} (toggle) 	 |
| GET    	 | /users/{id}/followers                 	 | Lista los usuarios que siguen a {id}                                	 |
| GET    	 | /users/{id}/following                 	 | Lista los usuarios a los que {id} sigue                             	 |
| GET    	 | /users/{id}/conversations             	 | Lista los usuarios con los que {id} ha intercambiado mensajes       	 |
| GET    	 | /users/{id}/conversations/{otherId}   	 | Obtiene los mensajes entre {id} y {otherId} (historial de chat)     	 |

### 📌 Ejemplo de follow

```
POST /api/users/5/follow?userId=1
Accept: application/json
```

Respuesta

```json
{
  "followerId": 1,
  "followedId": 5,
  "following": true
}
```

---

## 🐦 Posts

| Método 	 | Endpoint                                	 | Descripción                        	 |
|----------|-------------------------------------------|--------------------------------------|
| GET    	 | /posts                                  	 | Lista todos los posts              	 |
| GET    	 | /posts/{id}                             	 | Obtiene un post concreto           	 |
| POST   	 | /posts                                  	 | Crea un nuevo post                 	 |
| POST   	 | /posts/{id}/like?userId={idUsuario}     	 | Da o quita like al post (toggle)   	 |
| POST   	 | /posts/{id}/bookmark?userId={idUsuario} 	 | Guarda o desmarca el post (toggle) 	 |
| POST   	 | /posts/{id}/retweet?userId={idUsuario}  	 | Realiza o deshace retweet (toggle) 	 |

### 📌 Ejemplo de creación

```
POST /api/posts
Content-Type: application/json
Accept: application/json
```

Body:

```json
{
  "content": "Hola desde API Platform!",
  "user": "/api/users/1"
}
```

---

### 💬 Messages (Mensajes directos)

| Método 	| Endpoint       	| Descripción                    	|
|--------	|----------------	|--------------------------------	|
| GET    	| /messages      	| Lista todos los mensajes (DMs) 	|
| GET    	| /messages/{id} 	| Muestra un mensaje concreto    	|
| POST   	| /messages      	| Envía un nuevo mensaje directo 	|

### 📌 Ejemplo de envío

```
POST /api/messages
Content-Type: application/json
Accept: application/json
```

Body:

```json
{
  "sender": "/api/users/1",
  "receiver": "/api/users/2",
  "content": "¡Hola! Este es un mensaje de prueba."
}
```

---

## 🔖 Hashtags

| Método 	 | Endpoint             	 | Descripción                               	 |
|----------|------------------------|---------------------------------------------|
| GET    	 | /hashtags            	 | Lista todos los hashtags                  	 |
| GET    	 | /hashtags/{id}       	 | Obtiene la información de un hashtag      	 |
| GET    	 | /hashtags/{id}/posts 	 | Devuelve los posts asociados a un hashtag 	 |

### 📌 Ejemplo

```
GET /api/hashtags/3/posts
Accept: application/json
```

Respuesta:

```json
[
  {
    "id": 10,
    "content": "Probando #symfony",
    "likesCount": 2,
    "user": {
      "id": 1,
      "username": "demo"
    }
  }
]
```

---

## ❤️ Likes / 🔁 Retweets / 📑 Bookmarks

Estas acciones se realizan desde los endpoints de Post.

| Método 	 | Endpoint                                	 | Acción                      	 |
|----------|-------------------------------------------|-------------------------------|
| POST   	 | /posts/{id}/like?userId={idUsuario}     	 | Da o quita like             	 |
| POST   	 | /posts/{id}/retweet?userId={idUsuario}  	 | Hace o deshace retweet      	 |
| POST   	 | /posts/{id}/bookmark?userId={idUsuario} 	 | Guarda o quita de guardados 	 |

Cada endpoint devuelve el post actualizado con sus contadores.

---

## 👥 Follows

| Método 	 | Endpoint                                	 | Acción                      	 |
|----------|-------------------------------------------|-------------------------------|
| POST   	 | /posts/{id}/like?userId={idUsuario}     	 | Da o quita like             	 |
| POST   	 | /posts/{id}/retweet?userId={idUsuario}  	 | Hace o deshace retweet      	 |
| POST   	 | /posts/{id}/bookmark?userId={idUsuario} 	 | Guarda o quita de guardados 	 |

### 📌 Ejemplo de lectura

```
GET /api/users/1/followers
Accept: application/json
```

Respuesta:

```json
[
  { "id": 2, "username": "ana" },
  { "id": 3, "username": "pedro" }
]
```

---

## 🧠 Resumen de recursos principales

| Entidad                       	 | Lectura    	 | Escritura  	 | Endpoints adicionales                           	 |
|---------------------------------|--------------|--------------|---------------------------------------------------|
| User                          	 | ✅          	 | —          	 | /follow, /followers, /following, /conversations 	 |
| Post                          	 | ✅          	 | ✅          	 | /like, /bookmark, /retweet                      	 |
| Message                       	 | ✅          	 | ✅          	 | /conversations/{otherId}                        	 |
| Hashtag                       	 | ✅          	 | —          	 | /posts                                          	 |
| Follow                        	 | (interno)  	 | (interno)  	 | Gestionado por /follow                          	 |
| PostLike / Retweet / Bookmark 	 | (internos) 	 | (internos) 	 | Gestionados por endpoints custom                	 |

## 📘 Notas

- Todos los endpoints aceptan y devuelven **JSON clásico** (`application/json`).

- Las relaciones (user, post, hashtag) se representan mediante IRIs de API Platform (por ejemplo: `"user": "/api/users/1"`).

- Las operaciones toggle (`like`, `follow`, `bookmark`, `retweet`) devuelven el recurso actualizado o un JSON simple con estado.

## 🗂 Colección Postman

Disponible en ./provisioning/postman/MINI-TWITTER.postman_collection.json