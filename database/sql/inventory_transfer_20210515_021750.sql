--
-- PostgreSQL database dump
--

-- Dumped from database version 13.0
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
-- Name: inventory_transfer_in; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inventory_transfer_in (
    id integer NOT NULL,
    transfer_out_id integer NOT NULL,
    doc_no character varying,
    received character varying,
    received_date date,
    location_id integer,
    status integer DEFAULT 0 NOT NULL,
    publish date,
    created_at timestamp without time zone,
    created_by integer,
    updated_at timestamp without time zone,
    updated_by integer,
    deleted_at timestamp without time zone
);


ALTER TABLE public.inventory_transfer_in OWNER TO postgres;

--
-- Name: inventory_transfer_in_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inventory_transfer_in_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.inventory_transfer_in_id_seq OWNER TO postgres;

--
-- Name: inventory_transfer_in_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inventory_transfer_in_id_seq OWNED BY public.inventory_transfer_in.id;


--
-- Name: inventory_transfer_in_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inventory_transfer_in_items (
    id integer NOT NULL,
    inventory_transfer_id integer NOT NULL,
    inventory_transfer_out_item_id integer NOT NULL,
    qty integer DEFAULT 0 NOT NULL,
    notes character varying
);


ALTER TABLE public.inventory_transfer_in_items OWNER TO postgres;

--
-- Name: inventory_transfer_in_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inventory_transfer_in_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.inventory_transfer_in_items_id_seq OWNER TO postgres;

--
-- Name: inventory_transfer_in_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inventory_transfer_in_items_id_seq OWNED BY public.inventory_transfer_in_items.id;


--
-- Name: inventory_transfer_out; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inventory_transfer_out (
    id integer NOT NULL,
    doc_no character varying,
    location_id integer,
    location_destination integer,
    file character varying,
    operator character varying,
    approved_by integer,
    approved_at timestamp without time zone,
    publish date,
    created_at timestamp without time zone,
    created_by integer,
    updated_at timestamp without time zone,
    updated_by integer,
    deleted_at timestamp without time zone,
    status integer DEFAULT 0
);


ALTER TABLE public.inventory_transfer_out OWNER TO postgres;

--
-- Name: inventory_transfer_out_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inventory_transfer_out_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.inventory_transfer_out_id_seq OWNER TO postgres;

--
-- Name: inventory_transfer_out_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inventory_transfer_out_id_seq OWNED BY public.inventory_transfer_out.id;


--
-- Name: inventory_transfer_out_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inventory_transfer_out_items (
    id integer NOT NULL,
    inventory_id integer DEFAULT 0,
    inventory_transfer_id integer DEFAULT 0,
    qty integer DEFAULT 0,
    qty_parsial integer DEFAULT 0,
    notes character varying,
    status integer DEFAULT 0
);


ALTER TABLE public.inventory_transfer_out_items OWNER TO postgres;

--
-- Name: inventory_transfer_out_items _id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."inventory_transfer_out_items _id_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."inventory_transfer_out_items _id_seq" OWNER TO postgres;

--
-- Name: inventory_transfer_out_items _id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."inventory_transfer_out_items _id_seq" OWNED BY public.inventory_transfer_out_items.id;


--
-- Name: inventory_transfer_in id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_in ALTER COLUMN id SET DEFAULT nextval('public.inventory_transfer_in_id_seq'::regclass);


--
-- Name: inventory_transfer_in_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_in_items ALTER COLUMN id SET DEFAULT nextval('public.inventory_transfer_in_items_id_seq'::regclass);


--
-- Name: inventory_transfer_out id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_out ALTER COLUMN id SET DEFAULT nextval('public.inventory_transfer_out_id_seq'::regclass);


--
-- Name: inventory_transfer_out_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_out_items ALTER COLUMN id SET DEFAULT nextval('public."inventory_transfer_out_items _id_seq"'::regclass);


--
-- Name: inventory_transfer_in_items inventory_transfer_in_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_in_items
    ADD CONSTRAINT inventory_transfer_in_items_pkey PRIMARY KEY (id);


--
-- Name: inventory_transfer_in inventory_transfer_in_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_in
    ADD CONSTRAINT inventory_transfer_in_pkey PRIMARY KEY (id);


--
-- Name: inventory_transfer_out unique_inventory_transfer_out_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_out
    ADD CONSTRAINT unique_inventory_transfer_out_id PRIMARY KEY (id);


--
-- Name: inventory_transfer_out_items unique_inventory_transfer_out_items _id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_out_items
    ADD CONSTRAINT "unique_inventory_transfer_out_items _id" PRIMARY KEY (id);


--
-- Name: inventory_transfer_in unique_table1_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_in
    ADD CONSTRAINT unique_table1_id UNIQUE (id);


--
-- Name: inventory_transfer_in_items fk_inventory_transfer_in_inventory_transfer_in_items; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_in_items
    ADD CONSTRAINT fk_inventory_transfer_in_inventory_transfer_in_items FOREIGN KEY (inventory_transfer_id) REFERENCES public.inventory_transfer_in(id) MATCH FULL ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: inventory_transfer_out_items ifk_inventory_transfer_out_inventory_transfer_out_items; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_transfer_out_items
    ADD CONSTRAINT ifk_inventory_transfer_out_inventory_transfer_out_items FOREIGN KEY (inventory_transfer_id) REFERENCES public.inventory_transfer_out(id) MATCH FULL ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

