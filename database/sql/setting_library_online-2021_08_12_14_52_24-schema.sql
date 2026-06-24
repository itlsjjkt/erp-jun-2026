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
-- Name: setting_library_online; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.setting_library_online (
    id integer NOT NULL,
    user_id integer NOT NULL,
    approval json,
    created_by integer,
    created_at timestamp without time zone,
    updated_by integer,
    updated_at timestamp without time zone,
    total_approval integer,
    permission_create boolean,
    approval_create boolean,
    permission_edit boolean,
    approval_edit boolean,
    permission_delete boolean,
    approval_delete boolean
);


ALTER TABLE public.setting_library_online OWNER TO postgres;

--
-- Name: permission_library_online_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permission_library_online_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.permission_library_online_id_seq OWNER TO postgres;

--
-- Name: permission_library_online_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permission_library_online_id_seq OWNED BY public.setting_library_online.id;


--
-- Name: setting_library_online id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.setting_library_online ALTER COLUMN id SET DEFAULT nextval('public.permission_library_online_id_seq'::regclass);


--
-- Name: setting_library_online permission_library_online_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.setting_library_online
    ADD CONSTRAINT permission_library_online_pkey PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

