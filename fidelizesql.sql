create table usuario(
  idusuario bigserial primary key,
  nome varchar(255),
  email VARCHAR(255),
  tipo SMALLINT,
  senha VARCHAR(255),
  cpf VARCHAR(20),
  datacriacao DATE
);

create table restaurante(

  idrestaurante bigserial primary key,
  nome varchar(255),
  cnpj varchar(20),
  estado varchar(2),
  cidade varchar(100),
  endereco varchar(255),
  telefone varchar(20),
  usuario_idusuario bigint  
  
);

ALTER TABLE "public"."restaurante"
  ADD CONSTRAINT "fk_usuario_restaurante" FOREIGN KEY ("usuario_idusuario")
    REFERENCES "public"."usuario"("idusuario")
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

create table campanha (
  idcampanha bigserial primary key,
  restaurante_idrestaurante bigint, 
  nomecampanha varchar(255),
  datainicial date,
  datafinal date,
  qtde INTEGER,
  observacao text
);

ALTER TABLE "public"."campanha"
  ADD CONSTRAINT "fk_restaurante_campanha" FOREIGN KEY ("restaurante_idrestaurante")
    REFERENCES "public"."restaurante"("idrestaurante")
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

create table usuariocampanha(
  idusuariocampanha bigserial primary key,
  idrestaurantefk bigint,
  idusuariofk bigint,
  idcampanhafk bigint,
  utilizado boolean
);

ALTER TABLE "public"."usuariocampanha"
  ADD CONSTRAINT "fk_campanha_usuariocampanha" FOREIGN KEY ("idcampanhafk")
    REFERENCES "public"."campanha"("idcampanha")
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

create table usuariocampanhaitem(
  idusuariocampanhaitem bigserial primary key,
  idusuariocampanhafk BIGINT,
  data date
);

ALTER TABLE "public"."usuariocampanhaitem"
  ADD CONSTRAINT "fk_usuariocampanha_usuariocampanhaitem" FOREIGN KEY ("idusuariocampanhafk")
    REFERENCES "public"."usuariocampanha"("idusuariocampanha")
    ON DELETE RESTRICT
    ON UPDATE CASCADE;