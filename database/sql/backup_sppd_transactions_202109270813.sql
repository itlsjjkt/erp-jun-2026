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

--
-- Name: sppd_transactions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sppd_transactions (
    user_id integer NOT NULL,
    doc_no character varying(2044) NOT NULL,
    job_place character varying(100),
    job_area character varying(100),
    date_start date,
    date_end date,
    description text,
    contact character varying(2044),
    description_others text,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone,
    id integer NOT NULL,
    budget_by character varying(2044),
    advance_date_transfer date,
    advance_payment_type character varying(2044),
    "position" integer,
    type integer,
    step integer,
    status integer DEFAULT 0 NOT NULL,
    publish date,
    department_id integer
);


ALTER TABLE public.sppd_transactions OWNER TO postgres;

--
-- Name: sppd_transaction_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sppd_transaction_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sppd_transaction_id_seq OWNER TO postgres;

--
-- Name: sppd_transaction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sppd_transaction_id_seq OWNED BY public.sppd_transactions.id;


--
-- Name: sppd_transactions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sppd_transactions ALTER COLUMN id SET DEFAULT nextval('public.sppd_transaction_id_seq'::regclass);


--
-- Name: sppd_transactions sppd_transaction_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sppd_transactions
    ADD CONSTRAINT sppd_transaction_pkey PRIMARY KEY (id);


--
-- Name: sppd_transactions unique_sppd_transaction_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sppd_transactions
    ADD CONSTRAINT unique_sppd_transaction_id UNIQUE (id);


--
-- PostgreSQL database dump complete
--

