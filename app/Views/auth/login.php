<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>Servisní zápisník</title>
</head>
<body>

<h1>Servisní zápisník</h1>

<form method="post" action="./login">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div>
        <input type="text" name="login" placeholder="Login">
    </div>

    <div>
        <input type="password" name="password" placeholder="Heslo">
    </div>

    <button type="submit">Přihlásit</button>
</form>

</body>
</html>