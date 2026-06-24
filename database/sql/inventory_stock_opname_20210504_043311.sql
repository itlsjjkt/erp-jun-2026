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
-- Name: inventory_stock_opname; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inventory_stock_opname (
    id integer NOT NULL,
    inventory_id integer NOT NULL,
    actual_stock character varying NOT NULL,
    good_stock character varying NOT NULL,
    bad_stock character varying NOT NULL,
    notes text NOT NULL,
    created_by integer NOT NULL,
    created_at timestamp with time zone NOT NULL
);


ALTER TABLE public.inventory_stock_opname OWNER TO postgres;

--
-- Name: inventory_stockopname_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inventory_stockopname_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.inventory_stockopname_id_seq OWNER TO postgres;

--
-- Name: inventory_stockopname_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inventory_stockopname_id_seq OWNED BY public.inventory_stock_opname.id;


--
-- Name: inventory_stock_opname id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_stock_opname ALTER COLUMN id SET DEFAULT nextval('public.inventory_stockopname_id_seq'::regclass);


--
-- Name: inventory_stock_opname inventory_stock_opname_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_stock_opname
    ADD CONSTRAINT inventory_stock_opname_pkey PRIMARY KEY (id);


--
-- Name: index_inventory_id2; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX index_inventory_id2 ON public.inventory_stock_opname USING btree (inventory_id);


--
-- PostgreSQL database dump complete
--

