<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <script type="text/javascript">
const myPromise3000 = new Promise(function(){
    console.log("[myPromise3000] Выполнение асинхронной операции");
    setTimeout(()=>console.log("[myPromise3000] Завершение асинхронной операции"), 3000);
});
const myPromise1000 = new Promise(function(){
    console.log("[myPromise1000] Выполнение асинхронной операции");
    setTimeout(()=>console.log("[myPromise1000] Завершение асинхронной операции"), 1000);
});
const myPromise2000 = new Promise(function(){
    console.log("[myPromise2000] Выполнение асинхронной операции");
    setTimeout(()=>console.log("[myPromise2000] Завершение асинхронной операции"), 2000);
});

    </script>
</body>
</html>