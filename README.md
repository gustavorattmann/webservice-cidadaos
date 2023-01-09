# Webservice Cidadão

### Como fazer para rodar o Webservice?

Primeiro configure o arquivo **.env** usando o **.env.example** como modelo, coloque os dados do seu banco.

Depois de configurado, é necessário rodar o seguinte comando:
```php
php artisan key:generate
```

Em seguida, iremos criar o banco de dados utilizando migrate:
```php
php artisan migrate
```

Agora é só rodar a aplicação:
```php
php artisan serve
```

### Rotas:

Para consultar todos os cidadãos, acesse a rota:
```
http://127.0.0.1:8000/api/cidadao
```

Para consultar um cidadão específico, acesse a mesma rota, mas com o id do cidadão:
```
http://127.0.0.1:8000/api/cidadao/1
```

Para cadastrar um cidadão, acesse a rota:
```
http://127.0.0.1:8000/api/cidadao/cadastro
```

É necessário preencher o body da requisição com json. Segue um exemplo:
```json
{
    "nome": "Alexandre Sebastião Samuel Lima",
    "cpf": "124.106.574-86",
    "cep": "69054-337",
    "numero": "183",
    "complemento": null,
    "sexo": "M"
}
```
*"O complemento é o único dado que pode ser nulo".*

Para alterar um cidadão, acesse a rota com o id:
```
http://127.0.0.1:8000/api/cidadao/cadastro/alterar/1
```

É necessário preencher o body da requisição com json. Segue um exemplo:
```json
{
    "nome": "Alexandre Sebastião Samuel Lima Júnior",
    "numero": "110"
}
```
*"Preencher apenas os campos que deseja alterar".*

Para deletar um cidadão, apenas informe o id na rota:
```
http://127.0.0.1:8000/api/cidadao/deletar/1
```

*[Todos os dados para testes foram obtidos do **4devs**.](https://www.4devs.com.br)*