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


CREATE SEQUENCE public.library_online_activity_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 9223372036854775807
    CACHE 1;

ALTER SEQUENCE public.library_online_activity_id_seq
    OWNER TO postgres;

--
-- Name: library_online_activity; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.library_online_activity (
    activity_id integer DEFAULT nextval('public.library_online_activity_id_seq'::regclass) NOT NULL,
    library_id integer,
    action character varying(90),
    created_by integer,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.library_online_activity OWNER TO postgres;

--
-- PostgreSQL database dump complete
--

