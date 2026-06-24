--
-- PostgreSQL database dump
--

-- Dumped from database version 12.7
-- Dumped by pg_dump version 12.7

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

-- SEQUENCE: public.user_activity_history_id_seq
-- DROP SEQUENCE public.user_activity_history_id_seq;

CREATE SEQUENCE public.user_activity_history_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 9223372036854775807
    CACHE 1;

ALTER SEQUENCE public.user_activity_history_id_seq
    OWNER TO postgres;

--
-- Name: user_activity_histories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_activity_histories (
    activity_id integer DEFAULT nextval('public.user_activity_history_id_seq'::regclass) NOT NULL,
    library_id integer,
    library_id_history integer,
    action character varying(90),
    created_by integer,
    created_at timestamp without time zone,
    company_id integer,
    department_id integer
);


ALTER TABLE public.user_activity_histories OWNER TO postgres;

--
-- PostgreSQL database dump complete
--

