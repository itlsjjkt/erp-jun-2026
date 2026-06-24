--
-- PostgreSQL database dump
--

-- Dumped from database version 12.4
-- Dumped by pg_dump version 12.4

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

--
-- Name: library_online_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.library_online_history (
    id integer NOT NULL,
    library_id integer,
    name character varying(200),
    description character varying(600),
    file_name character varying(300),
    company_id integer,
    department_id integer,
    created_by integer,
    created_at timestamp without time zone
);


ALTER TABLE public.library_online_history OWNER TO postgres;

--
-- Name: library_online_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.library_online_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.library_online_history_id_seq OWNER TO postgres;

--
-- Name: library_online_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.library_online_history_id_seq OWNED BY public.library_online_history.id;


--
-- Name: library_online_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.library_online_history ALTER COLUMN id SET DEFAULT nextval('public.library_online_history_id_seq'::regclass);


--
-- Name: library_online_history library_online_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.library_online_history
    ADD CONSTRAINT library_online_history_pkey PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

