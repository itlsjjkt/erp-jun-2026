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
-- Name: approval_sppd; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.approval_sppd (
    employee_id character varying(2044) NOT NULL,
    id integer NOT NULL,
    approval json,
    created_by integer,
    created_at timestamp without time zone,
    updated_by integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.approval_sppd OWNER TO postgres;

--
-- Name: sppd_rule_approval_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sppd_rule_approval_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sppd_rule_approval_id_seq OWNER TO postgres;

--
-- Name: sppd_rule_approval_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sppd_rule_approval_id_seq OWNED BY public.approval_sppd.id;


--
-- Name: approval_sppd id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.approval_sppd ALTER COLUMN id SET DEFAULT nextval('public.sppd_rule_approval_id_seq'::regclass);


--
-- Name: approval_sppd approval_sppd_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.approval_sppd
    ADD CONSTRAINT approval_sppd_id_key UNIQUE (id);


--
-- Name: approval_sppd approval_sppd_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.approval_sppd
    ADD CONSTRAINT approval_sppd_pkey PRIMARY KEY (id);


--
-- Name: approval_sppd_id_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX approval_sppd_id_idx ON public.approval_sppd USING btree (id);


--
-- PostgreSQL database dump complete
--

