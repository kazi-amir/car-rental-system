CREATE DATABASE IF NOT EXISTS car_rental;
USE car_rental;

CREATE TABLE cars (
  id int(11) NOT NULL,
  make varchar(50) NOT NULL,
  model varchar(50) NOT NULL,
  year int(11) NOT NULL,
  color varchar(30) NOT NULL,
  price_per_day decimal(10,2) NOT NULL,
  available tinyint(1) DEFAULT 1,
  image_path varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO cars (id, make, model, year, color, price_per_day, available, image_path, created_at) VALUES
(1, 'BMW', 'HY67S', 2023, 'White', '20.00', 1, NULL, '2025-04-16 14:03:15'),
(2, 'BMW', 'DGAS76', 2021, 'Grey', '20.00', 0, NULL, '2025-04-16 14:03:42'),
(3, 'Porche', 'GFD45', 2022, 'Red', '30.00', 1, NULL, '2025-04-16 14:04:25'),
(4, 'Nissan', '6HD2E', 2023, 'Blue', '30.00', 1, NULL, '2025-04-16 14:05:31'),
(5, 'Lambo', '64YDE', 2020, 'Yellow', '40.00', 1, NULL, '2025-04-16 14:06:02'),
(6, 'Nissan', 'HDF45', 2021, 'White', '35.00', 1, NULL, '2025-04-16 14:07:02'),
(7, 'BMW', '67HGS', 2023, 'Black', '30.00', 1, NULL, '2025-04-16 19:20:18'),
(8, 'Lambo', 'H5BGS8', 2025, 'Red', '100.00', 0, NULL, '2025-04-16 19:21:11'),
(9, 'Volvo', 'GT93', 2023, 'Green', '150.00', 0, NULL, '2025-04-16 19:27:28'),
(10, 'Ford', '9T8D', 2021, 'Grey', '110.00', 1, NULL, '2025-04-16 19:28:22'),
(11, 'Fusion', 'SUV912', 2023, 'Yellow', '35.00', 1, NULL, '2025-04-16 19:28:38'),
(12, 'Ferrari', 'S4KI89', 2022, 'Red', '60.00', 1, NULL, '2025-04-16 19:30:24'),
(13, 'Ferrari', 'S6Y4O', 2022, 'Red', '60.00', 1, NULL, '2025-04-16 19:30:49'),
(14, 'NISSAN', 'S32TGT', 2018, 'White', '80.00', 1, NULL, '2025-04-16 19:31:59'),
(15, 'AUDI', 'HI59TO', 2018, 'White', '120.00', 1, NULL, '2025-04-16 19:33:27');

CREATE TABLE rentals (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  car_id int(11) NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  total_price decimal(10,2) NOT NULL,
  status enum('active','completed','cancelled') DEFAULT 'active',
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO rentals (id, user_id, car_id, start_date, end_date, total_price, status, created_at) VALUES
(1, 2, 1, '2025-04-16', '2025-04-16', '20.00', 'cancelled', '2025-04-16 14:08:06'),
(2, 2, 6, '2025-04-16', '2025-04-17', '70.00', 'cancelled', '2025-04-16 14:08:28'),
(3, 6, 5, '2025-04-16', '2025-04-30', '600.00', 'cancelled', '2025-04-16 15:13:11'),
(4, 6, 2, '2025-04-16', '2034-04-30', '66040.00', 'cancelled', '2025-04-16 15:14:52'),
(5, 7, 8, '2025-04-17', '2025-04-17', '100.00', 'active', '2025-04-16 19:23:17'),
(6, 8, 9, '2025-04-24', '2025-05-10', '2550.00', 'active', '2025-04-16 19:43:01'),
(7, 10, 7, '2025-04-17', '2025-04-17', '30.00', 'cancelled', '2025-04-17 03:11:33'),
(8, 10, 2, '2025-04-17', '2025-04-18', '40.00', 'active', '2025-04-17 03:12:31');

CREATE TABLE users (
  id int(11) NOT NULL,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  role enum('admin','customer') NOT NULL,
  balance decimal(10,2) DEFAULT 0.00,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO users (id, username, password, email, role, balance, created_at) VALUES
(1, 'admin', '$2y$10$zAEv3jU8gBGwmIRNXmX78Ox3dl1Yhy6XW72EzthfC1eDHPBmNF5j6', 'admin@carrental.com', 'admin', '0.00', '2025-04-16 15:03:53'),
(2, 'kazi_amir', '$2y$10$5UYW9y4R9flI7SCkREXA6eegbqc29uED9d9oYiPQ/w.wcODwMjZEK', 'kazi@mail.com', 'customer', '27.50', '2025-04-16 14:07:29'),
(4, 'tasnim07', '$2y$10$2v9qkenPo6g3LBlEsi3D3.ew8jx9ZaphZdlbt2FCOaBZpMh24qPfG', 'tasnim.ahamed.20@gmail.com', 'customer', '0.00', '2025-04-16 14:34:17'),
(6, 'saimur', '$2y$10$B3WTY1zxbbhTMbVD7oLML.WNKKUoKgcLU8sbYyV6wQMGK5k1QLEi6', 'saimurrahmanarnob@gmail.com', 'customer', '966650.00', '2025-04-16 15:12:20'),
(7, 'anonymoushi', '$2y$10$WEGAlmbeZLlcYYZeFP6bB.y.c1BxWo0CoTq/bF1dFCd5Wr7U3YKWS', 'anonymoushi@rental.com', 'admin', '0.00', '2025-04-16 19:20:36'),
(8, 'ujmoushi', '$2y$10$GyxZ1cAYlEIi9TFBeR404OouhCGvLe0x7pNQEZkATJUyWambJ1LHi', 'ujmoushi@carrental.com', 'customer', '997450.00', '2025-04-16 19:37:39'),
(9, 'abc', '$2y$10$B9nnHb.HwFXnxBAHNIr5yeVhwipMK7zPd7VupxkHUKe1hqEBuAPqi', 'abc@gmail.com', 'customer', '0.00', '2025-04-16 20:52:49'),
(10, 'jannatulferdus', '$2y$10$lqZiBJxw.hBlt.nP5JS94ubzmcCS7.O/vTpXOH5ECTyNRcSfSfA..', 'ferdus23105101012@diu.edu.bd', 'customer', '95.00', '2025-04-17 03:10:17');

ALTER TABLE cars
  ADD PRIMARY KEY (id);

ALTER TABLE rentals
  ADD PRIMARY KEY (id),
  ADD KEY user_id (user_id),
  ADD KEY car_id (car_id);

ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY username (username),
  ADD UNIQUE KEY email (email);

ALTER TABLE cars
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE rentals
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- sell_car PROCEDURE
DELIMITER //
CREATE PROCEDURE sell_car(IN u_id INT, IN c_id INT, IN s_date DATE, IN e_date DATE, IN t_price DECIMAL)
BEGIN
	INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price) VALUES (u_id, c_id, s_date, e_date, t_price);
    
    UPDATE cars SET available = FALSE WHERE id =c_id;
    
    UPDATE users SET balance = balance - t_price WHERE id = u_id;
    
END //
DELIMITER ;