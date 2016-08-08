
create table if not exists stuff (`id` int not null auto_increment, `status` varchar(20) not null default 'pending', primary key(`id`));

process 1:
- reads row 1 from table, sleeps, updates row 1, trying to read row 2, fails, 

process 2:
- trying to read row 1 from table, lock fails, moves on to row 2, update row 2





