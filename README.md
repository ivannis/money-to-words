# Introduction

Write a function which returns a dollar value written out in English words.

The function should handle all values from 0 to 1000, up to two decimal places. If there is any
ambiguity in the spec you are welcome to make a decision on an appropriate output and document it.

Include related unit tests to show that it works.

A few examples:

| Input | Output                             |
| ----- | ---------------------------------- |
| 0     | "zero dollars"                     |
| 0.12  | "twelve cents"                     |
| 10.55 | "ten dollars and fifty five cents" |
| 120   | "one hundred and twenty dollars"   |

# Requirements

If you don't want to use Docker as the basis for your running environment, you need to make sure that your operating environment meets the following requirements:
   
- PHP >= 7.4
- Composer >= 2.0.2

# Installation

Execute the following command to create a copy of the `money-to-words` project.

```
git clone git@github.com:ivannis/money-to-words.git
cd money-to-words
```

**Installation using docker:**
```
docker-compose build 
# or docker build -t money-converter:dev .
```

**Manual installation:**
```
composer install --no-dev -o
```

# Usage

Once installed, you can run the one of the following commands to convert a numeric value into it's English words.

**Using docker:**
```
docker-compose run --rm converter <number> --currency=<optional-value> --unit=<optional-value>
# or docker run --rm money-converter:dev <number> --currency=<optional-value> --unit=<optional-value>
```

**Manual:**
```
./convert <number> --currency=<optional-value> --unit=<optional-value> 
```

**Advice:**
> If you want to test it with negative numbers you should add a `--` before the numeric value. e.g: 
> - `docker-compose run --rm converter -- -342.56`
> - `./convert -- -5000042.0004`
 
# Tests

```
$ composer test
```