# start server
```
sail up -d
```

# migrate
```
sail artisan migrate
```

# run test
if fresh install please run this code before testing
```
    touch storage/database.testing.sqlite
```

```
sail artisan test
```
