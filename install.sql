drop database if exists billing;

create database billing CHARACTER SET utf8 collate utf8_general_ci;

use billing;

create table users(
  id SMALLINT AUTO_INCREMENT NOT NULL,
  username varchar(40) NOT NULL,
  userpassword VARCHAR(256) not null,
  PRIMARY KEY (id)
)ENGINE = InnoDB;

create view v_users as select id,username,userpassword
  from users;

create table income_users(
  id int AUTO_INCREMENT NOT NULL,
  number_transaction char(6) not null,
  users_id SMALLINT NOT NULL,
  money DOUBLE NOT NULL,
  date_add DATETIME DEFAULT now(),
  PRIMARY KEY (id),
  INDEX(users_id),
  FOREIGN KEY (users_id) REFERENCES users(id),
  UNIQUE number_transaction(number_transaction)
)ENGINE = InnoDB;

create table costs_users(
  id int AUTO_INCREMENT NOT NULL ,
  number_transaction char(6) not null,
  users_id SMALLINT NOT NULL,
  money DOUBLE NOT NULL,
  percentage DOUBLE NOT NULL,
  date_add DATETIME DEFAULT now(),
  PRIMARY KEY (id),
  INDEX(users_id),
  FOREIGN KEY (users_id) REFERENCES users(id),
  UNIQUE number_transaction(number_transaction)
) ENGINE = InnoDB;

create view v_income_users as
  SELECT u.username, inc.number_transaction,
    inc.money, inc.date_add,inc.id
  FROM income_users inc
    INNER join v_users u on inc.users_id = u.id;

create view v_costs_users as
  SELECT u.username, inc.number_transaction,
    inc.money, inc.date_add,inc.id,inc.percentage
  FROM costs_users inc
    INNER join v_users u on inc.users_id = u.id;


delimiter //
create function generate_hash(in_str VARCHAR(40))
  returns varchar(256)
  begin
    return sha2(compress(in_str),256);
  end//


delimiter //
create procedure add_users(IN in_user varchar(40),IN in_pass VARCHAR(40))
  begin
    insert into users(username, userpassword) VALUES (in_user,generate_hash(in_pass));
  end //

call add_users('user1','user1');


delimiter //
create function getUserId(in_user VARCHAR(40),in_pass VARCHAR(40))
  RETURNS SMALLINT
  BEGIN
    set @user_id = 0;
    select id into @user_id from v_users
    WHERE username = in_user and userpassword = generate_hash(in_pass);
    RETURN @user_id;
  END //


delimiter //
create function getPercentage(in_number double,in_percentage smallint)
  RETURNS DOUBLE
  begin
    RETURN (in_number*in_percentage)/100;
  end //


delimiter //
create function getIncome(in_user_id smallint)
  returns double
  begin
    set @income_money = 0;
    select money into @income_money
    from income_users where users_id = in_user_id
    order by date_add desc
    limit 1;
    return @income_money;
  end //


drop procedure if exists add_income;
DELIMITER //
CREATE PROCEDURE add_income(in in_user_id smallint, in in_money double)
  BEGIN
    SET autocommit = 0; -- Отключим автокоммит
    SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE; -- Изменим уровень транзакции

    BEGIN

      DECLARE EXIT HANDLER FOR SQLEXCEPTION
      BEGIN
        ROLLBACK;
      END;

      START TRANSACTION;

      SET @income_money = 0;

     select money into @income_money from income_users
        WHERE users_id = in_user_id order by date_add desc
      limit 1 for update;


      SET @income_money := @income_money + in_money;

      INSERT INTO  income_users(
        number_transaction,users_id,money
      ) VALUES (left(uuid(),6),in_user_id,round(@income_money,2));
      COMMIT;

    END;
  END //

call add_income(1,125);


drop procedure if exists add_cost;
DELIMITER //
CREATE PROCEDURE add_cost(in in_user_id smallint,
                          in in_money double,in_percentage SMALLINT)
  BEGIN
    set autocommit = 0; -- Отключим автокоммит
    SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;

    BEGIN
      DECLARE EXIT HANDLER FOR SQLEXCEPTION
      BEGIN
        ROLLBACK ;
        UNLOCK TABLES;
      END;

      START TRANSACTION ;

      LOCK TABLES costs_users WRITE;

      SET @percentage = 0;

      SELECT getPercentage(in_money,in_percentage) INTO @percentage;

      SET @cost = (in_money + @percentage)*-1; -- сумма сколько нужно снять

      CALL add_income(in_user_id,@cost);

      INSERT INTO costs_users(number_transaction,users_id,money,percentage)
      VALUES (left(uuid(),6),in_user_id,@cost,@percentage);

      COMMIT;
      UNLOCK TABLES;
    END;
  END//

call add_cost(1,25,1);

create user 'user_billing'@'localhost' IDENTIFIED by '123456789';

grant execute on billing.* to 'user_billing'@'localhost';
grant select on billing.v_costs_users to 'user_billing'@'localhost';
grant select on billing.v_income_users to 'user_billing'@'localhost';
