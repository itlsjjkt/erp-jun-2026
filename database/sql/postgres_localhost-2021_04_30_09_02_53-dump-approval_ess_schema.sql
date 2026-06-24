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
-- Name: approval_ess; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.approval_ess (
    id integer NOT NULL,
    employee_id character varying(255) NOT NULL,
    approval json NOT NULL,
    created_by integer,
    created_at timestamp without time zone,
    updated_by integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.approval_ess OWNER TO postgres;

--
-- Name: approval_ess_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.approval_ess_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.approval_ess_id_seq OWNER TO postgres;

--
-- Name: approval_ess_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.approval_ess_id_seq OWNED BY public.approval_ess.id;


--
-- Name: approval_ess id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.approval_ess ALTER COLUMN id SET DEFAULT nextval('public.approval_ess_id_seq'::regclass);


--
-- Name: approval_ess unique_approval_ess_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.approval_ess
    ADD CONSTRAINT unique_approval_ess_id PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

