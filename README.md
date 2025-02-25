# IB Website

## Setup

```
ddev start
cp .env.example .env
ddev composer install
ddev yarn install
```

## Import database

```
ddev import-db --srv ./dump.sql.gz
```


## Build

```
ddev yarn run dev
```

Starting the watcher script for automatic building assets during development

```
ddev yarn run watch
```

## Deploy

```
ddev dep deploy
```
