--
-- PostgreSQL database dump
--

-- Dumped from database version 13.1
-- Dumped by pg_dump version 13.1

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
-- Name: fpb; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fpb (
    id integer NOT NULL,
    doc_no character varying NOT NULL,
    operator character varying,
    department_id integer NOT NULL,
    location_id integer NOT NULL,
    file character varying,
    status integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    created_by integer NOT NULL,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    deleted_by integer,
    cost_center character varying,
    uuid character varying,
    updated_by integer,
    publish date,
    description text
);


ALTER TABLE public.fpb OWNER TO postgres;

--
-- Name: fpb_histories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fpb_histories (
    id integer NOT NULL,
    fpb_id integer NOT NULL,
    user_id integer NOT NULL,
    message character varying,
    created_at timestamp without time zone
);


ALTER TABLE public.fpb_histories OWNER TO postgres;

--
-- Name: fpb_histories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fpb_histories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fpb_histories_id_seq OWNER TO postgres;

--
-- Name: fpb_histories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fpb_histories_id_seq OWNED BY public.fpb_histories.id;


--
-- Name: fpb_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fpb_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fpb_id_seq OWNER TO postgres;

--
-- Name: fpb_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fpb_id_seq OWNED BY public.fpb.id;


--
-- Name: fpb_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fpb_items (
    id integer NOT NULL,
    fpb_id integer NOT NULL,
    inventory_id integer NOT NULL,
    qty integer DEFAULT 0 NOT NULL,
    notes character varying,
    status integer,
    "position" integer,
    step integer DEFAULT 1,
    last_approved_at timestamp without time zone,
    last_approved integer,
    updated_at timestamp without time zone,
    created_at timestamp without time zone
);


ALTER TABLE public.fpb_items OWNER TO postgres;

--
-- Name: fpb_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fpb_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fpb_items_id_seq OWNER TO postgres;

--
-- Name: fpb_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fpb_items_id_seq OWNED BY public.fpb_items.id;


--
-- Name: fpb id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb ALTER COLUMN id SET DEFAULT nextval('public.fpb_id_seq'::regclass);


--
-- Name: fpb_histories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb_histories ALTER COLUMN id SET DEFAULT nextval('public.fpb_histories_id_seq'::regclass);


--
-- Name: fpb_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb_items ALTER COLUMN id SET DEFAULT nextval('public.fpb_items_id_seq'::regclass);


--
-- Name: fpb_histories fpb_histories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb_histories
    ADD CONSTRAINT fpb_histories_pkey PRIMARY KEY (id);


--
-- Name: fpb fpb_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb
    ADD CONSTRAINT fpb_id_key UNIQUE (id);


--
-- Name: fpb_items fpb_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb_items
    ADD CONSTRAINT fpb_items_pkey PRIMARY KEY (id);


--
-- Name: fpb fpb_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb
    ADD CONSTRAINT fpb_pkey PRIMARY KEY (id);


--
-- Name: fpb_id_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fpb_id_idx ON public.fpb USING btree (id);


--
-- Name: fpb_histories fk_fpb_fpb_histories; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb_histories
    ADD CONSTRAINT fk_fpb_fpb_histories FOREIGN KEY (fpb_id) REFERENCES public.fpb(id) MATCH FULL ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: fpb_items fk_fpb_fpb_items; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fpb_items
    ADD CONSTRAINT fk_fpb_fpb_items FOREIGN KEY (fpb_id) REFERENCES public.fpb(id) MATCH FULL ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

