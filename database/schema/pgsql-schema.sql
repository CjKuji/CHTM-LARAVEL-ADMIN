--
-- PostgreSQL database dump
--

\restrict 3dQFIIkiO2ZIfmRfutdhDgfnb3qHVfJCydbVMqAzJ02rl4J59z1e1Chzhfw23dQ

-- Dumped from database version 17.6
-- Dumped by pg_dump version 18.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA public;


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- Name: booking_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.booking_status AS ENUM (
    'pending',
    'approved',
    'cancelled',
    'checked_in',
    'checked_out',
    'rejected',
    'archived'
);


--
-- Name: housekeeping_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.housekeeping_status AS ENUM (
    'pending',
    'in_progress',
    'completed',
    'inspected',
    'do_not_disturb'
);


--
-- Name: payment_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.payment_status AS ENUM (
    'pending',
    'paid',
    'failed',
    'refunded'
);


--
-- Name: room_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.room_status AS ENUM (
    'available',
    'occupied',
    'needs_cleaning',
    'cleaning',
    'inspected',
    'maintenance',
    'do_not_disturb'
);


--
-- Name: user_role; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_role AS ENUM (
    'user',
    'super_admin',
    'admin',
    'reservation',
    'frontoffice',
    'housekeeper'
);


--
-- Name: apply_booking_checkin_checkout_window(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.apply_booking_checkin_checkout_window() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
  -- Force check-in time to 15:00 (3 PM) using the provided date portion
  NEW.start_at := (NEW.start_at::date + time '15:00')::timestamp;

  -- Force check-out time to 11:00 (11 AM) using the provided date portion
  NEW.end_at := (NEW.end_at::date + time '11:00')::timestamp;

  -- Safety: ensure end is after start; if not, bump checkout to next day at 11:00
  if NEW.end_at <= NEW.start_at then
    NEW.end_at := (NEW.start_at::date + interval '1 day' + time '11:00')::timestamp;
  end if;

  return NEW;
end;
$$;


--
-- Name: encrypt_and_sync_user(uuid, text, text, text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.encrypt_and_sync_user(target_user_id uuid, plain_fname text, plain_lname text, plain_email text, target_role text DEFAULT 'user'::text) RETURNS text
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
    -- Pull your Laravel APP_KEY directly from this variable 
    raw_laravel_key CONSTANT TEXT := 'OdVOqiRWcJWLZAPDTXfAlOywbVQTYS9v0k1B5I7jG1w=';
    
    decoded_key BYTEA;
    deterministic_hash TEXT;
    enc_email TEXT;
    enc_fname TEXT;
    enc_lname TEXT;
    
    -- Dynamic Workspace variables
    iv BYTEA;
    raw_encrypted BYTEA;
    auth_tag BYTEA;
    ciphertext BYTEA;
BEGIN
    -- 1. Decode the master system key
    decoded_key := decode(raw_laravel_key, 'base64');
    
    -- 2. Mock a standard 16-byte authentication tag block for structural padding consistency
    auth_tag := decode('00000000000000000000000000000000', 'hex');

    -- -------------------------------------------------------------------------
    -- ENCRYPT EMAIL
    -- -------------------------------------------------------------------------
    iv := extensions.gen_random_bytes(12); -- Standard 12-byte IV for GCM framework alignment
    -- Use the database-supported 'aes' cipher block chaining mode
    ciphertext := extensions.encrypt_iv(plain_email::bytea, decoded_key, iv || decode('00000000', 'hex'), 'aes');
    enc_email := encode(iv || auth_tag || ciphertext, 'base64');

    -- -------------------------------------------------------------------------
    -- ENCRYPT FIRST NAME
    -- -------------------------------------------------------------------------
    iv := extensions.gen_random_bytes(12);
    ciphertext := extensions.encrypt_iv(plain_fname::bytea, decoded_key, iv || decode('00000000', 'hex'), 'aes');
    enc_fname := encode(iv || auth_tag || ciphertext, 'base64');

    -- -------------------------------------------------------------------------
    -- ENCRYPT LAST NAME
    -- -------------------------------------------------------------------------
    iv := extensions.gen_random_bytes(12);
    ciphertext := extensions.encrypt_iv(plain_lname::bytea, decoded_key, iv || decode('00000000', 'hex'), 'aes');
    enc_lname := encode(iv || auth_tag || ciphertext, 'base64');

    -- 3. Generate the deterministic index token (SHA256)
    deterministic_hash := encode(extensions.digest(plain_email, 'sha256'), 'hex');

    -- 4. Upsert cleanly into public.users
    INSERT INTO public.users (id, fname, lname, email, email_hash, role, created_at)
    VALUES (
        target_user_id, 
        enc_fname, 
        enc_lname, 
        enc_email, 
        deterministic_hash, 
        target_role::user_role, 
        now()
    )
    ON CONFLICT (id) 
    DO UPDATE SET 
        fname      = EXCLUDED.fname,
        lname      = EXCLUDED.lname,
        email      = EXCLUDED.email,
        email_hash = EXCLUDED.email_hash,
        role       = EXCLUDED.role;

    RETURN 'Success: Profile fields encrypted and synchronized for UUID ' || target_user_id::text;
EXCEPTION WHEN OTHERS THEN
    RETURN 'Error during execution: ' || SQLERRM;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: amenities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.amenities (
    id integer NOT NULL,
    name text NOT NULL,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: amenities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.amenities ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.amenities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: archived_bookings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.archived_bookings (
    id integer NOT NULL,
    original_booking_id integer NOT NULL,
    user_id uuid NOT NULL,
    room_id integer NOT NULL,
    start_at timestamp without time zone NOT NULL,
    guests integer DEFAULT 1,
    status text NOT NULL,
    message text,
    checked_in_at timestamp without time zone,
    checked_out_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    price_at_booking numeric,
    total_amount numeric,
    extra_beds integer DEFAULT 0,
    has_child boolean DEFAULT false,
    child_age_group text,
    has_pwd boolean DEFAULT false,
    has_senior boolean DEFAULT false,
    guest_fname text,
    guest_lname text,
    room_number text,
    approved_by uuid,
    rejected_by uuid,
    checked_in_by uuid,
    checked_out_by uuid,
    room_type_name text,
    room_type_id integer,
    room_capacity integer,
    room_base_price numeric,
    room_floor integer,
    end_at timestamp without time zone,
    payment_method text,
    guest_email_hash character varying(64)
);


--
-- Name: archived_bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.archived_bookings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.archived_bookings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    entity_type text,
    entity_id integer,
    action text,
    old_value jsonb,
    new_value jsonb,
    changed_by uuid,
    reason text,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: bookings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bookings (
    id integer NOT NULL,
    user_id uuid NOT NULL,
    room_id integer NOT NULL,
    start_at timestamp without time zone NOT NULL,
    guests integer DEFAULT 1,
    extra_beds integer DEFAULT 0,
    price_at_booking numeric NOT NULL,
    total_amount numeric NOT NULL,
    message text,
    checked_in_at timestamp without time zone,
    checked_out_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    status public.booking_status DEFAULT 'pending'::public.booking_status,
    approved_by uuid,
    rejected_by uuid,
    checked_in_by uuid,
    checked_out_by uuid,
    has_child boolean DEFAULT false,
    child_age_group text,
    has_pwd boolean DEFAULT false,
    has_senior boolean DEFAULT false,
    end_at timestamp without time zone,
    payment_method text,
    room_type text,
    CONSTRAINT bookings_payment_mode_check CHECK (((payment_method IS NULL) OR (lower(payment_method) = ANY (ARRAY['gcash'::text, 'cash'::text])))),
    CONSTRAINT bookings_room_type_check CHECK (((room_type IS NULL) OR (lower(room_type) = ANY (ARRAY['standard'::text, 'deluxe'::text]))))
);


--
-- Name: bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.bookings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.bookings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: housekeeping_task_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.housekeeping_task_items (
    id integer NOT NULL,
    task_id integer,
    item_name text,
    is_done boolean DEFAULT false,
    quantity integer DEFAULT 1,
    note text
);


--
-- Name: housekeeping_task_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.housekeeping_task_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: housekeeping_task_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.housekeeping_task_items_id_seq OWNED BY public.housekeeping_task_items.id;


--
-- Name: housekeeping_tasks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.housekeeping_tasks (
    id integer NOT NULL,
    room_id integer,
    booking_id integer,
    assigned_to uuid,
    started_at timestamp without time zone,
    completed_at timestamp without time zone,
    note text,
    template_id integer,
    status public.housekeeping_status DEFAULT 'pending'::public.housekeeping_status,
    created_at timestamp without time zone DEFAULT now(),
    duration_minutes integer,
    completed_by uuid
);


--
-- Name: housekeeping_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.housekeeping_tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: housekeeping_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.housekeeping_tasks_id_seq OWNED BY public.housekeeping_tasks.id;


--
-- Name: housekeeping_template_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.housekeeping_template_items (
    id integer NOT NULL,
    template_id integer NOT NULL,
    item_name text NOT NULL,
    default_quantity integer DEFAULT 1,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: housekeeping_template_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.housekeeping_template_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: housekeeping_template_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.housekeeping_template_items_id_seq OWNED BY public.housekeeping_template_items.id;


--
-- Name: housekeeping_templates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.housekeeping_templates (
    id integer NOT NULL,
    room_type_id integer NOT NULL,
    name text NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: housekeeping_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.housekeeping_templates_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: housekeeping_templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.housekeeping_templates_id_seq OWNED BY public.housekeeping_templates.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: room_amenities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.room_amenities (
    room_type_id integer NOT NULL,
    amenity_id integer NOT NULL
);


--
-- Name: room_images; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.room_images (
    id integer NOT NULL,
    room_id integer NOT NULL,
    image_url text NOT NULL,
    display_order integer DEFAULT 0
);


--
-- Name: room_images_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.room_images ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.room_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: room_status_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.room_status_logs (
    id integer NOT NULL,
    room_id integer NOT NULL,
    changed_by uuid,
    note text,
    created_at timestamp without time zone DEFAULT now(),
    status public.room_status DEFAULT 'available'::public.room_status
);


--
-- Name: room_status_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.room_status_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: room_status_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.room_status_logs_id_seq OWNED BY public.room_status_logs.id;


--
-- Name: room_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.room_types (
    id integer NOT NULL,
    name character varying NOT NULL,
    description text,
    capacity integer NOT NULL,
    base_price numeric NOT NULL,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: room_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.room_types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.room_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: rooms; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rooms (
    id integer NOT NULL,
    room_type_id integer NOT NULL,
    room_number character varying NOT NULL,
    floor integer,
    price_override numeric,
    status public.room_status DEFAULT 'available'::public.room_status,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: rooms_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.rooms ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.rooms_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.settings (
    key text NOT NULL,
    value text
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id uuid NOT NULL,
    fname character varying NOT NULL,
    lname character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    role public.user_role DEFAULT 'user'::public.user_role,
    email text,
    email_hash character varying(64),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: users_backup; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users_backup (
    id uuid,
    fname character varying(255),
    created_at timestamp without time zone,
    lname character varying
);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: housekeeping_task_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_task_items ALTER COLUMN id SET DEFAULT nextval('public.housekeeping_task_items_id_seq'::regclass);


--
-- Name: housekeeping_tasks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks ALTER COLUMN id SET DEFAULT nextval('public.housekeeping_tasks_id_seq'::regclass);


--
-- Name: housekeeping_template_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_template_items ALTER COLUMN id SET DEFAULT nextval('public.housekeeping_template_items_id_seq'::regclass);


--
-- Name: housekeeping_templates id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_templates ALTER COLUMN id SET DEFAULT nextval('public.housekeeping_templates_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: room_status_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_status_logs ALTER COLUMN id SET DEFAULT nextval('public.room_status_logs_id_seq'::regclass);


--
-- Name: amenities amenities_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.amenities
    ADD CONSTRAINT amenities_name_key UNIQUE (name);


--
-- Name: amenities amenities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.amenities
    ADD CONSTRAINT amenities_pkey PRIMARY KEY (id);


--
-- Name: archived_bookings archived_bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archived_bookings
    ADD CONSTRAINT archived_bookings_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- Name: housekeeping_task_items housekeeping_task_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_task_items
    ADD CONSTRAINT housekeeping_task_items_pkey PRIMARY KEY (id);


--
-- Name: housekeeping_tasks housekeeping_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks
    ADD CONSTRAINT housekeeping_tasks_pkey PRIMARY KEY (id);


--
-- Name: housekeeping_template_items housekeeping_template_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_template_items
    ADD CONSTRAINT housekeeping_template_items_pkey PRIMARY KEY (id);


--
-- Name: housekeeping_templates housekeeping_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_templates
    ADD CONSTRAINT housekeeping_templates_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: room_amenities room_amenities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_amenities
    ADD CONSTRAINT room_amenities_pkey PRIMARY KEY (room_type_id, amenity_id);


--
-- Name: room_images room_images_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_images
    ADD CONSTRAINT room_images_pkey PRIMARY KEY (id);


--
-- Name: room_status_logs room_status_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_status_logs
    ADD CONSTRAINT room_status_logs_pkey PRIMARY KEY (id);


--
-- Name: room_types room_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_types
    ADD CONSTRAINT room_types_pkey PRIMARY KEY (id);


--
-- Name: rooms rooms_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_pkey PRIMARY KEY (id);


--
-- Name: rooms rooms_room_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_room_number_key UNIQUE (room_number);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (key);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: archived_bookings_email_hash_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX archived_bookings_email_hash_idx ON public.archived_bookings USING btree (guest_email_hash);


--
-- Name: idx_archived_bookings_room_type_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archived_bookings_room_type_id ON public.archived_bookings USING btree (room_type_id);


--
-- Name: users_email_hash_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_email_hash_idx ON public.users USING btree (email_hash);


--
-- Name: archived_bookings fk_archived_approved_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archived_bookings
    ADD CONSTRAINT fk_archived_approved_by FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- Name: archived_bookings fk_archived_checked_in_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archived_bookings
    ADD CONSTRAINT fk_archived_checked_in_by FOREIGN KEY (checked_in_by) REFERENCES public.users(id);


--
-- Name: archived_bookings fk_archived_checked_out_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archived_bookings
    ADD CONSTRAINT fk_archived_checked_out_by FOREIGN KEY (checked_out_by) REFERENCES public.users(id);


--
-- Name: housekeeping_tasks fk_assigned_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks
    ADD CONSTRAINT fk_assigned_user FOREIGN KEY (assigned_to) REFERENCES public.users(id);


--
-- Name: bookings fk_bookings_approved_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT fk_bookings_approved_by FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- Name: bookings fk_bookings_checked_in_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT fk_bookings_checked_in_by FOREIGN KEY (checked_in_by) REFERENCES public.users(id);


--
-- Name: bookings fk_bookings_checked_out_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT fk_bookings_checked_out_by FOREIGN KEY (checked_out_by) REFERENCES public.users(id);


--
-- Name: bookings fk_bookings_rejected_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT fk_bookings_rejected_by FOREIGN KEY (rejected_by) REFERENCES public.users(id);


--
-- Name: bookings fk_bookings_room; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES public.rooms(id);


--
-- Name: bookings fk_bookings_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: housekeeping_tasks fk_completed_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks
    ADD CONSTRAINT fk_completed_user FOREIGN KEY (completed_by) REFERENCES public.users(id);


--
-- Name: housekeeping_tasks fk_housekeeping_completed_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks
    ADD CONSTRAINT fk_housekeeping_completed_by FOREIGN KEY (completed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: room_status_logs fk_room; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_status_logs
    ADD CONSTRAINT fk_room FOREIGN KEY (room_id) REFERENCES public.rooms(id);


--
-- Name: room_status_logs fk_room_status_room; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_status_logs
    ADD CONSTRAINT fk_room_status_room FOREIGN KEY (room_id) REFERENCES public.rooms(id) ON DELETE CASCADE;


--
-- Name: housekeeping_templates fk_room_type; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_templates
    ADD CONSTRAINT fk_room_type FOREIGN KEY (room_type_id) REFERENCES public.room_types(id);


--
-- Name: housekeeping_task_items fk_task_items_task; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_task_items
    ADD CONSTRAINT fk_task_items_task FOREIGN KEY (task_id) REFERENCES public.housekeeping_tasks(id) ON DELETE CASCADE;


--
-- Name: housekeeping_tasks fk_template; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks
    ADD CONSTRAINT fk_template FOREIGN KEY (template_id) REFERENCES public.housekeeping_templates(id);


--
-- Name: housekeeping_template_items fk_template; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_template_items
    ADD CONSTRAINT fk_template FOREIGN KEY (template_id) REFERENCES public.housekeeping_templates(id);


--
-- Name: housekeeping_tasks housekeeping_tasks_room_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.housekeeping_tasks
    ADD CONSTRAINT housekeeping_tasks_room_id_fkey FOREIGN KEY (room_id) REFERENCES public.rooms(id);


--
-- Name: room_amenities room_amenities_amenity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_amenities
    ADD CONSTRAINT room_amenities_amenity_id_fkey FOREIGN KEY (amenity_id) REFERENCES public.amenities(id);


--
-- Name: room_amenities room_amenities_room_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_amenities
    ADD CONSTRAINT room_amenities_room_type_id_fkey FOREIGN KEY (room_type_id) REFERENCES public.room_types(id);


--
-- Name: room_images room_images_room_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.room_images
    ADD CONSTRAINT room_images_room_id_fkey FOREIGN KEY (room_id) REFERENCES public.rooms(id);


--
-- Name: rooms rooms_room_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_room_type_id_fkey FOREIGN KEY (room_type_id) REFERENCES public.room_types(id);


--
-- Name: users users_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_id_fkey FOREIGN KEY (id) REFERENCES auth.users(id);


--
-- PostgreSQL database dump complete
--

\unrestrict 3dQFIIkiO2ZIfmRfutdhDgfnb3qHVfJCydbVMqAzJ02rl4J59z1e1Chzhfw23dQ

