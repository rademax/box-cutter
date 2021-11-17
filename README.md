# Getting started

## Installation

To deploy the project you need to perform next commands in project directory:

- Give write permission for the storage folder and the cache folder within the bootstrap folder.
```
sudo chmod 777 -R storage/
sudo chmod 777 -R bootstrap/cache/
```
- Install project dependencies (for avoid the installing Composer globally.)
```
docker run --rm -v $(pwd):/app composer install
```
- Create .env file
```
cp .env.example .env
```
- Start containers
```
docker-compose up -d
```
Finally, you can access the application on http://localhost:80

## Testing
To perform tests, run command:
```
docker-compose exec app ./vendor/bin/phpunit
```

## API Documentation

**Get instructions to cut boxes**
----
Returns json unique point list data.

* **URL**

  /api/simple_box

* **Method:**

  `POST`

*  **URL Params**

   `sheetSize=[array]`
   `boxSize=[array]`

* **Success Response:**

    * **Code:** 200 <br />
      **Content:**
```
    {
        success: true,
        amount: 1,
        program: [
            { 
                command : "GOTO", 
                x : 0, 
                y : 0
            }
        ]
    }
```

* **Error Response:**

    * **Code:** 422 <br />
      **Content:** 
    * 
```
    {
        success: false,
        error: "Invalid input format. Please use only positive integers"
    }
```

## Methodology

Due to the lack of time, cutting of several boxes per sheet was implemented, but the amount of waste was not minimized

## Next steps

In the future, it is planned to add algorithm to minimize the amount of paper waste.
