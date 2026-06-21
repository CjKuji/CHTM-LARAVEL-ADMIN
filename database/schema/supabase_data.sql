--
-- PostgreSQL database dump
--

\restrict JPjwjIzdBPErSS62wxaEvdhpQAaTfOaJrY2fKmcgn8Xujzi76gkUKT5p0K5JfTr

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
-- Data for Name: amenities; Type: TABLE DATA; Schema: public; Owner: postgres
--

SET SESSION AUTHORIZATION DEFAULT;

ALTER TABLE public.amenities DISABLE TRIGGER ALL;

INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (1, 'Air Conditioning', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (2, 'TV', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (3, 'Smart TV', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (4, '2 Single Beds', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (5, '2 King Beds', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (6, 'Cabinet', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (7, 'Shower', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (8, 'Premium Shower', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (9, 'Bathtub', '2026-04-21 03:19:27.713916');
INSERT INTO public.amenities OVERRIDING SYSTEM VALUE VALUES (10, 'Mini Sala', '2026-04-21 03:19:27.713916');


ALTER TABLE public.amenities ENABLE TRIGGER ALL;

--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.users DISABLE TRIGGER ALL;

INSERT INTO public.users VALUES ('563988d2-776d-4a14-aa78-7cdedf4066b9', 'Luis', 'Gabriel Cari�o', '2026-03-07 02:15:21.838823', 'user', NULL, NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('e2c9708d-1d50-4f78-bec8-5e057c3df8a7', 'Kiel', 'Thomas Warmey', '2026-04-05 07:44:19.101822', 'user', NULL, NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('ba444ea6-bb98-4024-ae0d-510fa3b10b43', 'mExi3vCZQwPdwdwgLVgCc/f9O2+xUQtR1MMjnUwRU8k=', '1qkCUFJSfZX6bWr9vMa1ReD1IWlwdcl+QJzOsriNsG+faeUdzH0Pgw==', '2026-05-20 23:50:14', 'frontoffice', '0lcXqbSygBIzsL/aoD+Fy/CK12k0NUKkY5xJzXPupZiOj1DSiiudSoDNjyX7wg==', 'b43e97bff3f8a42c7dfb3ef2b479fa53056989db6c4de101d4259e859e8fcf8c', '2026-06-07 07:47:54+00');
INSERT INTO public.users VALUES ('23a08633-38a2-4b95-926d-ea9af49ce7f2', '/XUBcj7jZLnoLU3rzU3VMJ9ZZxPq6LRi+AdfPGQ56JE=', 'reGBEGEKuex3fJBaMXSJK8P7J5ELp7nXHxBzrn4/L/cO', '2026-04-20 00:32:55', 'admin', '7NX6wBhE/zMncSzHYMj50SZWFsz61Anjohhw7BahMSfankeCypgi+bcnc5YAULSDHThgZe+Y', '4575296db8cede6559cc42a617ef501e6269ab756d93d743ca0926ce0087ca91', '2026-06-07 15:42:11+00');
INSERT INTO public.users VALUES ('faab2c43-092c-474c-8c54-2cbd27552a02', 'Icon', 'Zeus Gonzales', '2026-04-03 06:22:40.982907', 'user', '202310429@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('0546918a-6cea-41a9-8970-d2e7a199639d', 'Bugero,', 'Carl james L.', '2026-03-07 08:15:15.075649', 'user', '202310393@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('06e9e861-2aec-4121-8903-39ced9904679', 'John', 'Lourence Lingad', '2026-03-07 09:19:36.196908', 'user', '202310508@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('4f93d2ed-ebd3-4ca3-a73c-05b782421a59', 'Aca-ac,', 'John Hero', '2026-03-10 06:32:31.888646', 'user', '202310951@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('3c0aaff5-9524-484b-8b84-4c041ff477d2', 'Mark', 'Joheun Kim', '2026-04-05 04:53:18.976317', 'user', '202312255@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('31f4d155-90a2-4692-9bb6-8692b1d19c6f', 'Joferson', 'Ursua', '2026-03-28 17:53:48.996341', 'user', '202312267@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('7e961e79-b102-4900-b8f2-4690e711c397', 'Reggie', 'Hallare', '2026-04-04 13:50:09.965232', 'user', '201511113@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('65d4f763-c25d-49e9-9791-01bc25df3ebd', 'CHTM', 'ADMIN', '2026-03-07 19:07:48', 'user', NULL, NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('3fdbc133-a252-44df-a5b2-ef19047919c9', 'CHTM', 'RESERVATION', '2026-05-21 01:32:01', 'reservation', NULL, NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('3e222f72-735a-4ba5-86dc-302c42c0e484', 'CHTM', 'HOUSEKEEPING', '2026-05-21 01:33:38', 'housekeeper', NULL, NULL, '2026-06-07 03:53:15.034578+00');
INSERT INTO public.users VALUES ('d0a4b51d-c108-4db6-b737-1ef6b0086c7e', 'Aleczander', 'Mendoza', '2026-04-28 07:37:44.064975', 'user', '202311255@gordoncollege.edu.ph', NULL, '2026-06-07 03:53:15.034578+00');

                                
ALTER TABLE public.users ENABLE TRIGGER ALL;

--
-- Data for Name: archived_bookings; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.archived_bookings DISABLE TRIGGER ALL;

INSERT INTO public.archived_bookings OVERRIDING SYSTEM VALUE VALUES (15, 29, '7e961e79-b102-4900-b8f2-4690e711c397', 11, '2026-04-26 07:00:00', 4, 'checked_out', NULL, '2026-04-28 06:00:19.148', '2026-04-28 06:00:22.762', '2026-04-28 06:00:23.480063', NULL, 4245, 1, true, 'under2', true, false, 'Hallare,', 'Reggie', '102', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Deluxe Room', 2, 4, 4500, 1, '2026-04-27 03:00:00', 'cash', NULL);
INSERT INTO public.archived_bookings OVERRIDING SYSTEM VALUE VALUES (16, 29, '7e961e79-b102-4900-b8f2-4690e711c397', 11, '2026-04-26 07:00:00', 4, 'checked_out', NULL, '2026-04-28 06:00:19.148', '2026-04-28 06:00:22.762', '2026-04-28 06:00:24.471042', NULL, 4245, 1, true, 'under2', true, false, 'Hallare,', 'Reggie', '102', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Deluxe Room', 2, 4, 4500, 1, '2026-04-27 03:00:00', 'cash', NULL);
INSERT INTO public.archived_bookings OVERRIDING SYSTEM VALUE VALUES (17, 31, 'd0a4b51d-c108-4db6-b737-1ef6b0086c7e', 11, '2026-05-01 07:00:00', 3, 'checked_out', NULL, '2026-04-28 07:45:48.36', '2026-04-28 07:46:27.369', '2026-04-28 07:46:30.9487', NULL, 29205, 1, true, 'over2', true, false, 'Aleczander', 'Gabriel G. Mendoza', '102', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Deluxe Room', 2, 4, 4500, 1, '2026-05-08 03:00:00', 'gcash', NULL);
INSERT INTO public.archived_bookings OVERRIDING SYSTEM VALUE VALUES (18, 30, '4f93d2ed-ebd3-4ca3-a73c-05b782421a59', 10, '2026-04-27 07:00:00', 2, 'checked_out', NULL, '2026-05-21 01:47:59.98', '2026-05-21 01:48:09.261', '2026-05-21 01:48:15.609821', NULL, 1876.5, 0, true, 'under2', true, false, 'Aca-ac,', 'John Hero', '101', '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Standard Room', 1, 2, 2500, 1, '2026-04-28 03:00:00', 'cash', NULL);
INSERT INTO public.archived_bookings OVERRIDING SYSTEM VALUE VALUES (19, 33, 'd0a4b51d-c108-4db6-b737-1ef6b0086c7e', 10, '2026-05-07 07:00:00', 3, 'checked_out', NULL, '2026-05-21 20:59:12.232', '2026-05-21 20:59:17.67', '2026-05-21 20:59:18.874172', NULL, 2585, 0, true, NULL, false, false, 'Aleczander', 'Mendoza', '101', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Standard Room', 1, 2, 2500, 1, '2026-05-08 03:00:00', 'cash', NULL);
INSERT INTO public.archived_bookings OVERRIDING SYSTEM VALUE VALUES (20, 32, 'd0a4b51d-c108-4db6-b737-1ef6b0086c7e', 10, '2026-04-28 07:00:00', 4, 'checked_out', NULL, '2026-05-22 06:48:04.428', '2026-05-22 07:23:30.79', '2026-05-22 07:23:32.163904', NULL, 3212.75, 1, true, 'over2', false, true, 'Aleczander', 'Mendoza', '101', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Standard Room', 1, 2, 2500, 1, '2026-04-29 03:00:00', 'cash', NULL);


ALTER TABLE public.archived_bookings ENABLE TRIGGER ALL;

--
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.audit_logs DISABLE TRIGGER ALL;

INSERT INTO public.audit_logs VALUES (1, 'rooms', 10, 'UPDATE', '{"floor": 1, "status": "available", "room_number": "101", "room_type_id": 1}', '{"floor": "2", "status": "available", "room_number": "101", "room_type_id": "1"}', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '2026-06-07 07:55:28.858631');
INSERT INTO public.audit_logs VALUES (2, 'rooms', 10, 'UPDATE', '{"floor": 2, "status": "available", "room_number": "101", "room_type_id": 1}', '{"floor": "1", "status": "available", "room_number": "101", "room_type_id": "1"}', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '2026-06-07 07:56:24.298436');


ALTER TABLE public.audit_logs ENABLE TRIGGER ALL;

--
-- Data for Name: room_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.room_types DISABLE TRIGGER ALL;

INSERT INTO public.room_types OVERRIDING SYSTEM VALUE VALUES (1, 'Standard Room', 'Comfortable standard room with essential amenities', 2, 2500, '2026-04-21 03:19:38.975247');
INSERT INTO public.room_types OVERRIDING SYSTEM VALUE VALUES (2, 'Deluxe Room', 'Spacious deluxe room with premium amenities', 4, 4500, '2026-04-21 03:19:38.975247');


ALTER TABLE public.room_types ENABLE TRIGGER ALL;

--
-- Data for Name: rooms; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.rooms DISABLE TRIGGER ALL;

INSERT INTO public.rooms OVERRIDING SYSTEM VALUE VALUES (11, 2, '102', 1, NULL, 'available', '2026-04-22 04:58:16.629345');
INSERT INTO public.rooms OVERRIDING SYSTEM VALUE VALUES (10, 1, '101', 1, NULL, 'available', '2026-04-22 04:58:09.024329');


ALTER TABLE public.rooms ENABLE TRIGGER ALL;

--
-- Data for Name: bookings; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.bookings DISABLE TRIGGER ALL;

INSERT INTO public.bookings OVERRIDING SYSTEM VALUE VALUES (33, 'd0a4b51d-c108-4db6-b737-1ef6b0086c7e', 10, '2026-05-04 23:00:00', 2, 0, 2585, 2585, NULL, '2026-05-21 20:59:12.232', '2026-05-21 20:59:17.67', '2026-04-28 08:29:38.43922', 'checked_out', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', true, NULL, false, false, '2026-05-05 19:00:00', 'cash', 'standard');
INSERT INTO public.bookings OVERRIDING SYSTEM VALUE VALUES (29, '7e961e79-b102-4900-b8f2-4690e711c397', 11, '2026-04-25 23:00:00', 4, 1, 4245, 4245, NULL, '2026-04-28 06:00:19.148', '2026-04-28 06:00:22.762', '2026-04-26 15:40:43.071613', 'checked_out', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', false, NULL, false, false, '2026-04-26 19:00:00', 'cash', 'deluxe');
INSERT INTO public.bookings OVERRIDING SYSTEM VALUE VALUES (30, '4f93d2ed-ebd3-4ca3-a73c-05b782421a59', 10, '2026-04-27 07:00:00', 2, 0, 2085, 2085, NULL, '2026-05-21 01:47:59.98', '2026-05-21 01:48:09.261', '2026-04-27 15:37:51.703502', 'checked_out', '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', true, 'under2', true, false, '2026-04-28 03:00:00', 'cash', 'standard');
INSERT INTO public.bookings OVERRIDING SYSTEM VALUE VALUES (31, 'd0a4b51d-c108-4db6-b737-1ef6b0086c7e', 11, '2026-04-30 23:00:00', 3, 1, 29205, 209335, NULL, '2026-04-28 07:45:48.36', '2026-04-28 07:46:27.369', '2026-04-28 07:43:18.320167', 'checked_out', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', false, NULL, false, false, '2026-05-07 19:00:00', 'gcash', 'deluxe');
INSERT INTO public.bookings OVERRIDING SYSTEM VALUE VALUES (28, '4f93d2ed-ebd3-4ca3-a73c-05b782421a59', 10, '2026-04-30 07:00:00', 2, 0, 14085, 14085, NULL, '2026-04-23 06:55:35.265', '2026-04-23 06:55:54.81', '2026-04-23 06:45:42.620958', 'pending', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', true, 'under2', true, true, '2026-05-07 03:00:00', 'cash', 'standard');
INSERT INTO public.bookings OVERRIDING SYSTEM VALUE VALUES (32, 'd0a4b51d-c108-4db6-b737-1ef6b0086c7e', 10, '2026-04-27 23:00:00', 3, 1, 2645, 3168.67, NULL, '2026-05-22 06:48:04.428', '2026-05-22 07:23:30.79', '2026-04-28 08:00:51.731602', 'checked_out', '23a08633-38a2-4b95-926d-ea9af49ce7f2', NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '23a08633-38a2-4b95-926d-ea9af49ce7f2', true, 'over2', false, true, '2026-04-28 19:00:00', 'cash', 'standard');


ALTER TABLE public.bookings ENABLE TRIGGER ALL;

--
-- Data for Name: housekeeping_templates; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.housekeeping_templates DISABLE TRIGGER ALL;

INSERT INTO public.housekeeping_templates VALUES (1, 1, 'Standard Cleaning', 'Basic cleaning for standard room', '2026-04-21 07:33:05.240723');
INSERT INTO public.housekeeping_templates VALUES (2, 2, 'Deluxe Cleaning', 'Full cleaning for deluxe room', '2026-04-21 07:33:05.240723');


ALTER TABLE public.housekeeping_templates ENABLE TRIGGER ALL;

--
-- Data for Name: housekeeping_tasks; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.housekeeping_tasks DISABLE TRIGGER ALL;

INSERT INTO public.housekeeping_tasks VALUES (30, 10, NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '2026-05-21 20:18:18.914', '2026-05-21 20:35:11.663', 'Auto-generated from Make Up Room request', 1, 'completed', '2026-05-21 20:16:40.307904', 497, '23a08633-38a2-4b95-926d-ea9af49ce7f2');
INSERT INTO public.housekeeping_tasks VALUES (31, 10, 33, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '2026-05-21 20:59:31.02', '2026-05-21 20:59:48.829', '', 1, 'completed', '2026-05-21 20:59:17.930456', 480, '23a08633-38a2-4b95-926d-ea9af49ce7f2');
INSERT INTO public.housekeeping_tasks VALUES (32, 10, NULL, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '2026-05-21 21:29:40.75', '2026-05-21 21:30:08.16', 'Auto-generated from Make Up Room request', 1, 'completed', '2026-05-21 21:29:26.48301', 480, '23a08633-38a2-4b95-926d-ea9af49ce7f2');
INSERT INTO public.housekeeping_tasks VALUES (33, 10, 32, '23a08633-38a2-4b95-926d-ea9af49ce7f2', '2026-05-22 07:23:45.4', '2026-05-22 07:23:51.038', '', 1, 'completed', '2026-05-22 07:23:31.416169', 480, '23a08633-38a2-4b95-926d-ea9af49ce7f2');
INSERT INTO public.housekeeping_tasks VALUES (34, 10, NULL, NULL, NULL, NULL, 'Auto-generated from Make Up Room request', 1, 'pending', '2026-05-22 08:57:02.45858', NULL, NULL);


ALTER TABLE public.housekeeping_tasks ENABLE TRIGGER ALL;

--
-- Data for Name: housekeeping_task_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.housekeeping_task_items DISABLE TRIGGER ALL;

INSERT INTO public.housekeeping_task_items VALUES (288, 30, 'Replace pillowcases', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (289, 30, 'Sweep and mop floor', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (290, 30, 'Dust furniture (cabinet, table)', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (291, 30, 'Clean toilet and sink', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (292, 30, 'Clean shower area', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (293, 30, 'Check air conditioning', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (294, 30, 'Check TV', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (295, 30, 'Restock toiletries', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (296, 30, 'Replace towels', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (297, 30, 'Change bedsheets (2 single beds)', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (298, 31, 'Replace pillowcases', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (299, 31, 'Sweep and mop floor', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (300, 31, 'Dust furniture (cabinet, table)', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (301, 31, 'Clean toilet and sink', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (302, 31, 'Clean shower area', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (303, 31, 'Check air conditioning', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (304, 31, 'Check TV', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (305, 31, 'Restock toiletries', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (306, 31, 'Replace towels', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (307, 31, 'Change bedsheets (2 single beds)', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (309, 32, 'Sweep and mop floor', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (308, 32, 'Replace pillowcases', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (312, 32, 'Clean shower area', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (313, 32, 'Check air conditioning', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (310, 32, 'Dust furniture (cabinet, table)', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (315, 32, 'Restock toiletries', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (316, 32, 'Replace towels', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (317, 32, 'Change bedsheets (2 single beds)', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (311, 32, 'Clean toilet and sink', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (314, 32, 'Check TV', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (320, 33, 'Dust furniture (cabinet, table)', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (325, 33, 'Restock toiletries', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (319, 33, 'Sweep and mop floor', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (323, 33, 'Check air conditioning', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (318, 33, 'Replace pillowcases', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (327, 33, 'Change bedsheets (2 single beds)', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (322, 33, 'Clean shower area', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (321, 33, 'Clean toilet and sink', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (326, 33, 'Replace towels', true, 2, '');
INSERT INTO public.housekeeping_task_items VALUES (324, 33, 'Check TV', true, 1, '');
INSERT INTO public.housekeeping_task_items VALUES (328, 34, 'Replace pillowcases', false, 2, NULL);
INSERT INTO public.housekeeping_task_items VALUES (329, 34, 'Sweep and mop floor', false, 1, NULL);
INSERT INTO public.housekeeping_task_items VALUES (330, 34, 'Dust furniture (cabinet, table)', false, 1, NULL);
INSERT INTO public.housekeeping_task_items VALUES (331, 34, 'Clean toilet and sink', false, 1, NULL);
INSERT INTO public.housekeeping_task_items VALUES (332, 34, 'Clean shower area', false, 1, NULL);
INSERT INTO public.housekeeping_task_items VALUES (333, 34, 'Check air conditioning', false, 1, NULL);
INSERT INTO public.housekeeping_task_items VALUES (334, 34, 'Check TV', false, 1, NULL);
INSERT INTO public.housekeeping_task_items VALUES (335, 34, 'Restock toiletries', false, 2, NULL);
INSERT INTO public.housekeeping_task_items VALUES (336, 34, 'Replace towels', false, 2, NULL);
INSERT INTO public.housekeeping_task_items VALUES (337, 34, 'Change bedsheets (2 single beds)', false, 2, NULL);


ALTER TABLE public.housekeeping_task_items ENABLE TRIGGER ALL;

--
-- Data for Name: housekeeping_template_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.housekeeping_template_items DISABLE TRIGGER ALL;

INSERT INTO public.housekeeping_template_items VALUES (2, 1, 'Replace pillowcases', 2, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (3, 1, 'Sweep and mop floor', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (4, 1, 'Dust furniture (cabinet, table)', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (5, 1, 'Clean toilet and sink', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (6, 1, 'Clean shower area', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (7, 1, 'Check air conditioning', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (8, 1, 'Check TV', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (9, 1, 'Restock toiletries', 2, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (10, 1, 'Replace towels', 2, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (11, 2, 'Change bedsheets (2 king beds)', 2, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (12, 2, 'Replace pillowcases', 4, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (13, 2, 'Vacuum carpet / floor', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (14, 2, 'Dust furniture (mini sala, cabinet)', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (15, 2, 'Clean toilet and sink', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (16, 2, 'Clean bathtub', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (17, 2, 'Clean premium shower', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (18, 2, 'Check air conditioning', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (19, 2, 'Check Smart TV', 1, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (20, 2, 'Restock toiletries (premium)', 3, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (21, 2, 'Replace towels (bath + face)', 4, '2026-04-21 07:33:19.560966');
INSERT INTO public.housekeeping_template_items VALUES (1, 1, 'Change bedsheets (2 single beds)', 2, '2026-04-21 07:33:19.560966');


ALTER TABLE public.housekeeping_template_items ENABLE TRIGGER ALL;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: migrator_user
--

ALTER TABLE public.migrations DISABLE TRIGGER ALL;

INSERT INTO public.migrations VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations VALUES (4, '2026_05_17_073006_create_personal_access_tokens_table', 1);
INSERT INTO public.migrations VALUES (5, '2026_06_02_000001_extend_users_for_chtm', 1);
INSERT INTO public.migrations VALUES (6, '2026_06_02_000002_create_hotel_core_tables', 1);
INSERT INTO public.migrations VALUES (7, '2026_06_02_000003_add_room_flags_and_receipts', 1);


ALTER TABLE public.migrations ENABLE TRIGGER ALL;

--
-- Data for Name: room_amenities; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.room_amenities DISABLE TRIGGER ALL;

INSERT INTO public.room_amenities VALUES (1, 1);
INSERT INTO public.room_amenities VALUES (1, 2);
INSERT INTO public.room_amenities VALUES (1, 4);
INSERT INTO public.room_amenities VALUES (1, 6);
INSERT INTO public.room_amenities VALUES (1, 7);
INSERT INTO public.room_amenities VALUES (2, 1);
INSERT INTO public.room_amenities VALUES (2, 3);
INSERT INTO public.room_amenities VALUES (2, 5);
INSERT INTO public.room_amenities VALUES (2, 6);
INSERT INTO public.room_amenities VALUES (2, 8);
INSERT INTO public.room_amenities VALUES (2, 9);
INSERT INTO public.room_amenities VALUES (2, 10);


ALTER TABLE public.room_amenities ENABLE TRIGGER ALL;

--
-- Data for Name: room_images; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.room_images DISABLE TRIGGER ALL;



ALTER TABLE public.room_images ENABLE TRIGGER ALL;

--
-- Data for Name: room_status_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.room_status_logs DISABLE TRIGGER ALL;

INSERT INTO public.room_status_logs VALUES (1, 10, NULL, 'Auto-marked dirty after checkout', '2026-04-22 05:09:06.743031', 'needs_cleaning');
INSERT INTO public.room_status_logs VALUES (2, 10, NULL, 'Auto dirty after checkout + template task generated', '2026-04-22 05:13:10.120714', 'needs_cleaning');
INSERT INTO public.room_status_logs VALUES (3, 10, NULL, 'Auto dirty after checkout', '2026-04-22 05:31:25.889848', 'needs_cleaning');
INSERT INTO public.room_status_logs VALUES (4, 10, '23a08633-38a2-4b95-926d-ea9af49ce7f2', 'Auto dirty after checkout', '2026-04-22 05:38:49.639786', 'needs_cleaning');


ALTER TABLE public.room_status_logs ENABLE TRIGGER ALL;

--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.settings DISABLE TRIGGER ALL;



ALTER TABLE public.settings ENABLE TRIGGER ALL;

--
-- Data for Name: users_backup; Type: TABLE DATA; Schema: public; Owner: postgres
--

ALTER TABLE public.users_backup DISABLE TRIGGER ALL;

INSERT INTO public.users_backup VALUES ('563988d2-776d-4a14-aa78-7cdedf4066b9', 'Luis', '2026-03-07 02:15:21.838823', 'Gabriel Cari�o');
INSERT INTO public.users_backup VALUES ('0546918a-6cea-41a9-8970-d2e7a199639d', 'Bugero,', '2026-03-07 08:15:15.075649', 'Carl james L.');
INSERT INTO public.users_backup VALUES ('65d4f763-c25d-49e9-9791-01bc25df3ebd', 'CHTM', '2026-03-07 19:07:48', 'ADMIN');
INSERT INTO public.users_backup VALUES ('a6109eaa-b2c3-430d-ac91-6fb5c700d3ea', 'BSIT', '2026-03-07 18:29:02', 'ADMIN');
INSERT INTO public.users_backup VALUES ('06e9e861-2aec-4121-8903-39ced9904679', 'John', '2026-03-07 09:19:36.196908', 'Lourence Lingad');
INSERT INTO public.users_backup VALUES ('4f93d2ed-ebd3-4ca3-a73c-05b782421a59', 'Aca-ac,', '2026-03-10 06:32:31.888646', 'John Hero');


ALTER TABLE public.users_backup ENABLE TRIGGER ALL;

--
-- Name: amenities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.amenities_id_seq', 20, true);


--
-- Name: archived_bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.archived_bookings_id_seq', 20, true);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 2, true);


--
-- Name: bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bookings_id_seq', 33, true);


--
-- Name: housekeeping_task_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.housekeeping_task_items_id_seq', 337, true);


--
-- Name: housekeeping_tasks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.housekeeping_tasks_id_seq', 34, true);


--
-- Name: housekeeping_template_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.housekeeping_template_items_id_seq', 24, true);


--
-- Name: housekeeping_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.housekeeping_templates_id_seq', 2, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrator_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 7, true);


--
-- Name: room_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.room_images_id_seq', 1, false);


--
-- Name: room_status_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.room_status_logs_id_seq', 4, true);


--
-- Name: room_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.room_types_id_seq', 2, true);


--
-- Name: rooms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rooms_id_seq', 13, true);


--
-- PostgreSQL database dump complete
--

\unrestrict JPjwjIzdBPErSS62wxaEvdhpQAaTfOaJrY2fKmcgn8Xujzi76gkUKT5p0K5JfTr

