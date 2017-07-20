<?php
class View
{
    static function viewAuth(){
        echo '<html lang="ru"><head><meta charset="utf-8"/><meta http-equiv="x-ua-compatible" content="ie=edge"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.3.1/css/foundation.min.css"/></head><body><div class="row"><div class="medium-7 columns"><form action="/auth" method="POST"><div class="row"><div class="medium-7 columns"><label>Имя пользователя<input type="text" name="username"/></label></div></div><div class="row"><div class="medium-7 columns"><label>Пароль<input type="password" name="password"/></label></div></div><div class="row"><div class="medium-7 columns"><input type="hidden" name="action" value="auth"/><input class="button" type="submit" value="Войти"/></div></div></form></div></div></body></html>';
    }

    static function viewCabinet($income){
        echo '<html lang="ru"><head><meta charset="utf-8"/>
    <meta http-equiv="x-ua-compatible" content="ie=edge"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.3.1/css/foundation.min.css"/></head><body><div class="row"><div class="medium-8 columns"><h4>Баланс:'.$income.'</h4></div></div><div class="row"><div class="medium-5 columns">
    <form action="/" method="POST"><div class="row"><div class="medium-7 columns"><label>Пополнить<input type="text" name="income"/></label></div></div><div class="row"><div class="medium-7 columns"><input type="hidden" name="action" value="income"/><input class="button" type="submit" value="Пополнить"/></div></div></form></div><div class="medium-5 columns">
    <form action="/" method="POST"><div class="row"><div class="medium-7 columns"><label>Вывести<input type="text" name="income"/></label></div></div><div class="row"><div class="medium-7 columns"><input type="hidden" name="action" value="costs"/><input class="button" type="submit" value="Вывести"/></div></div></form></div>
    <div class="medium-2 columns"></div></div></body></html>';
    }

}