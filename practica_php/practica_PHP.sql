drop table sinistres_linies;
drop table sinistres;
drop sequence sinistres_seq;


create table SINISTRES(
	CODI NUMBER(15) constraint sinistres_pk primary key,
	CURSA varchar2(15),
	VEHICLE varchar2(10),
	PERSONATGE varchar2(15),
	dataReparacio date,
	gravetat Number(1),	
	Constraint sinistres_curses_fk FOREIGN KEY (cursa) References curses(codi),
	Constraint sinistres_vehicle_fk FOREIGN KEY (vehicle) references vehicles(codi),
	Constraint sinistres_personatge_fk FOREIGN key	(personatge) references personatges(alias)
);




create table Sinistres_linies(
	LINIA NUMBER(15),
	SINISTRE NUMBER(15),
	EINA VARchAR2(10),
	COST DECIMAL(10,2),
	COMPATIBILITAT DECIMAL(3,1),
	CONSTRAINT  linies_sinistre_fk FOREIGN KEY (sinistre) references sinistres(codi),
	CONSTRAINT 	liniesSin_eines_fk FOREIGN KEY (EINA) references eines(codi),
	CONSTRAINT  Sinistre_linies_PK PRIMARY KEY (LINIA,SINISTRE)
);

create sequence sinistres_seq
	start with 1
	increment by 1
	nocache
	nocycle;
	
	insert into sinistres(codi,Cursa,vehicle,Personatge,dataRepara,gravetat)
	select 
	from participantsCurses p left outer join sinistres s on p.cursa=s.cursa 
	

