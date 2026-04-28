-- Catch Jiu Jitsu - PostgreSQL schema for Supabase
-- Run this in Supabase SQL Editor to create tables, then import data

-- Enable UUID extension (Supabase has this by default)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Tables (order matters for foreign keys)
CREATE TABLE users (
  id BIGSERIAL PRIMARY KEY,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255),
  email VARCHAR(255) NOT NULL UNIQUE,
  phone VARCHAR(50),
  chinese_name VARCHAR(255),
  belt_color VARCHAR(50) DEFAULT 'White Belt',
  line_id VARCHAR(255),
  line_notify_token VARCHAR(500),
  gender VARCHAR(20) NOT NULL DEFAULT 'male' CHECK (gender IN ('male','female','other')),
  age_group VARCHAR(20) NOT NULL DEFAULT 'Adults' CHECK (age_group IN ('Kids','Adults')),
  membership_package_id BIGINT,
  membership_status VARCHAR(20) NOT NULL DEFAULT 'none' CHECK (membership_status IN ('active','expired','pending','none')),
  membership_expires_at DATE,
  classes_remaining INT,
  membership_expiry_reminder_sent_at DATE,
  classes_zero_reminder_sent_at TIMESTAMP,
  last_reengagement_line_sent_at TIMESTAMP,
  dob DATE,
  email_verified_at TIMESTAMP,
  password VARCHAR(255) NOT NULL,
  rank VARCHAR(20) DEFAULT 'White' CHECK (rank IN ('White','Grey','Yellow','Orange','Green','Blue','Purple','Brown','Black')),
  belt_variation VARCHAR(20) CHECK (belt_variation IN ('white','solid','black')),
  stripes INT NOT NULL DEFAULT 0,
  mat_hours INT NOT NULL DEFAULT 0,
  monthly_class_goal INT NOT NULL DEFAULT 12,
  monthly_hours_goal INT NOT NULL DEFAULT 15,
  reminders_enabled BOOLEAN NOT NULL DEFAULT true,
  public_profile BOOLEAN NOT NULL DEFAULT false,
  locale VARCHAR(10) DEFAULT 'en',
  is_admin BOOLEAN NOT NULL DEFAULT false,
  is_coach BOOLEAN NOT NULL DEFAULT false,
  accepting_private_classes BOOLEAN NOT NULL DEFAULT false,
  private_class_price DECIMAL(10,2),
  discount_type VARCHAR(20) NOT NULL DEFAULT 'none' CHECK (discount_type IN ('none','gratis','fixed','percentage','half_price')),
  discount_amount INT NOT NULL DEFAULT 0,
  discount_percentage INT NOT NULL DEFAULT 0,
  avatar_url VARCHAR(255),
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE membership_packages (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  duration_type VARCHAR(20) NOT NULL DEFAULT 'months' CHECK (duration_type IN ('days','weeks','months','years','classes')),
  duration_value INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  age_group VARCHAR(20) NOT NULL DEFAULT 'All' CHECK (age_group IN ('Adults','Kids','All')),
  allowed_days VARCHAR(20),
  is_active BOOLEAN NOT NULL DEFAULT true,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE families (
  id BIGSERIAL PRIMARY KEY,
  primary_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE family_members (
  id BIGSERIAL PRIMARY KEY,
  family_id BIGINT NOT NULL REFERENCES families(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE UNIQUE,
  role VARCHAR(20) NOT NULL DEFAULT 'parent',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(family_id, user_id)
);

CREATE TABLE classes (
  id BIGSERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  title_zh VARCHAR(255),
  type VARCHAR(20) NOT NULL CHECK (type IN ('Gi','No-Gi','Open Mat','Fundamentals')),
  age_group VARCHAR(20) NOT NULL DEFAULT 'Adults' CHECK (age_group IN ('Kids','Adults','All')),
  start_time TIMESTAMP NOT NULL,
  duration_minutes INT NOT NULL DEFAULT 60,
  instructor_name VARCHAR(255) NOT NULL,
  capacity INT NOT NULL DEFAULT 20,
  is_cancelled BOOLEAN NOT NULL DEFAULT false,
  instructor_id BIGINT REFERENCES users(id),
  recurrence_id BIGINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE bookings (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  class_id BIGINT NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
  booked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  checked_in BOOLEAN NOT NULL DEFAULT false,
  UNIQUE(user_id, class_id)
);

CREATE TABLE class_trials (
  id BIGSERIAL PRIMARY KEY,
  class_id BIGINT NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
  name VARCHAR(255) NOT NULL,
  age SMALLINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE coach_availability (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  day_of_week SMALLINT NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  slot_duration_minutes SMALLINT NOT NULL DEFAULT 60,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE payments (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  amount DECIMAL(8,2) NOT NULL,
  month VARCHAR(255) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Overdue' CHECK (status IN ('Pending Verification','Paid','Overdue','Rejected')),
  payment_method VARCHAR(20) CHECK (payment_method IN ('bank','linepay')),
  payment_date DATE,
  account_last_5 VARCHAR(5),
  proof_image_path VARCHAR(255),
  submitted_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE products (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  product_name_zh VARCHAR(255),
  category VARCHAR(60) NOT NULL,
  description TEXT,
  product_desc_zh TEXT,
  price DECIMAL(10,2) NOT NULL,
  image_url VARCHAR(500),
  is_preorder BOOLEAN NOT NULL DEFAULT false,
  preorder_weeks SMALLINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE product_variants (
  id BIGSERIAL PRIMARY KEY,
  product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  size VARCHAR(20) NOT NULL,
  color VARCHAR(60),
  stock_quantity INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE orders (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  total_price DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  notes TEXT,
  payment_method VARCHAR(20),
  account_last_5 VARCHAR(5),
  payment_submitted_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE order_items (
  id BIGSERIAL PRIMARY KEY,
  order_id BIGINT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  product_variant_id BIGINT NOT NULL REFERENCES product_variants(id) ON DELETE CASCADE,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  is_preorder BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE private_class_bookings (
  id BIGSERIAL PRIMARY KEY,
  coach_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  member_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  scheduled_at TIMESTAMP NOT NULL,
  duration_minutes SMALLINT NOT NULL DEFAULT 60,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  price DECIMAL(10,2),
  requested_at TIMESTAMP,
  responded_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Laravel system tables
CREATE TABLE cache (
  key VARCHAR(255) PRIMARY KEY,
  value TEXT NOT NULL,
  expiration INT NOT NULL
);

CREATE TABLE cache_locks (
  key VARCHAR(255) PRIMARY KEY,
  owner VARCHAR(255) NOT NULL,
  expiration INT NOT NULL
);

CREATE TABLE sessions (
  id VARCHAR(255) PRIMARY KEY,
  user_id BIGINT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  payload TEXT NOT NULL,
  last_activity INT NOT NULL
);

CREATE TABLE job_batches (
  id VARCHAR(255) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  total_jobs INT NOT NULL,
  pending_jobs INT NOT NULL,
  failed_jobs INT NOT NULL,
  failed_job_ids TEXT NOT NULL,
  options TEXT,
  cancelled_at INT,
  created_at INT NOT NULL,
  finished_at INT
);

CREATE TABLE jobs (
  id BIGSERIAL PRIMARY KEY,
  queue VARCHAR(255) NOT NULL,
  payload TEXT NOT NULL,
  attempts SMALLINT NOT NULL,
  reserved_at INT,
  available_at INT NOT NULL,
  created_at INT NOT NULL
);

CREATE TABLE failed_jobs (
  id BIGSERIAL PRIMARY KEY,
  uuid VARCHAR(255) NOT NULL UNIQUE,
  connection TEXT NOT NULL,
  queue TEXT NOT NULL,
  payload TEXT NOT NULL,
  exception TEXT NOT NULL,
  failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE migrations (
  id SERIAL PRIMARY KEY,
  migration VARCHAR(255) NOT NULL,
  batch INT NOT NULL
);

CREATE TABLE password_reset_tokens (
  email VARCHAR(255) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_classes_start_time ON classes(start_time);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_class_id ON bookings(class_id);
CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_last_activity ON sessions(last_activity);
