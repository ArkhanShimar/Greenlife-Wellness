-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 01:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `greenlife_wellness`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `client_name`, `client_email`, `client_phone`, `service_id`, `therapist_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(1, 'arkhan', 'arkhansimar1@gmail.com', '0761006149', 1, 1, '2025-06-25', '11:00:00', 'completed', '2025-06-18 16:12:17'),
(2, 'arkhan', 'arkhansimar1@gmail.com', '0761006149', 3, 5, '2025-07-05', '14:00:00', 'cancelled', '2025-06-21 08:10:33'),
(3, 'arkhan', 'arkhansimar1@gmail.com', '0761006149', 5, 1, '2025-07-04', '16:00:00', 'cancelled', '2025-06-21 08:47:38'),
(7, 'arkhan', 'arkhansimar1@gmail.com', '12345678', 7, 2, '2025-06-30', '10:00:00', 'pending', '2025-06-28 05:50:34'),
(8, 'arkhan', 'arkhansimar1@gmail.com', '12345678', 8, 2, '2025-06-28', '14:00:00', 'pending', '2025-06-28 07:15:42'),
(9, 'Sahl', 'sahl@gmail.com', '0762345678', 9, 7, '2025-06-30', '14:00:00', 'confirmed', '2025-06-29 10:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `author` varchar(100) DEFAULT 'GreenLife Wellness',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('published','draft') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `content`, `excerpt`, `image_path`, `category`, `author`, `created_at`, `updated_at`, `status`) VALUES
(1, '10 Essential Wellness Tips for a Healthier Lifestyle', '<p>Maintaining a healthy lifestyle is crucial for overall well-being. Here are 10 essential tips that can help you achieve better health:</p>\r\n    \r\n    <h3>1. Stay Hydrated</h3>\r\n    <p>Drink at least 8 glasses of water daily. Proper hydration helps with digestion, energy levels, and skin health.</p>\r\n    \r\n    <h3>2. Get Quality Sleep</h3>\r\n    <p>Aim for 7-9 hours of sleep per night. Quality sleep is essential for mental clarity, immune function, and emotional balance.</p>\r\n    \r\n    <h3>3. Exercise Regularly</h3>\r\n    <p>Engage in at least 30 minutes of moderate exercise daily. This can include walking, yoga, swimming, or any activity you enjoy.</p>\r\n    \r\n    <h3>4. Eat a Balanced Diet</h3>\r\n    <p>Include plenty of fruits, vegetables, lean proteins, and whole grains in your diet. Avoid processed foods and excessive sugar.</p>\r\n    \r\n    <h3>5. Practice Mindfulness</h3>\r\n    <p>Take time each day for meditation or deep breathing exercises. This helps reduce stress and improve mental clarity.</p>\r\n    \r\n    <h3>6. Maintain Social Connections</h3>\r\n    <p>Strong social relationships contribute to better mental health and longevity. Make time for friends and family.</p>\r\n    \r\n    <h3>7. Limit Screen Time</h3>\r\n    <p>Reduce time spent on electronic devices, especially before bedtime. This improves sleep quality and reduces eye strain.</p>\r\n    \r\n    <h3>8. Practice Good Posture</h3>\r\n    <p>Maintain proper posture throughout the day to prevent back pain and improve breathing.</p>\r\n    \r\n    <h3>9. Take Regular Breaks</h3>\r\n    <p>If you work at a desk, take short breaks every hour to stretch and move around.</p>\r\n    \r\n    <h3>10. Practice Gratitude</h3>\r\n    <p>Keep a gratitude journal or take time each day to reflect on things you\'re thankful for.</p>\r\n    \r\n    <p>Remember, small changes can lead to significant improvements in your overall wellness. Start with one or two tips and gradually incorporate more into your daily routine.</p>', 'Discover 10 essential wellness tips that can transform your lifestyle and improve your overall health and well-being.', 'assets/images/blog/wellness-tips.webp', 'Wellness Tips', 'GreenLife Wellness', '2025-06-22 11:07:24', '2025-06-22 11:07:24', 'published'),
(2, 'The Power of Mindfulness in Daily Life', '<p>Mindfulness is more than just a buzzword – it\'s a powerful practice that can transform your daily life and improve your mental well-being.</p>\r\n    \r\n    <h3>What is Mindfulness?</h3>\r\n    <p>Mindfulness is the practice of being fully present in the moment, aware of where you are and what you\'re doing, without being overwhelmed by what\'s happening around you.</p>\r\n    \r\n    <h3>Benefits of Mindfulness</h3>\r\n    <ul>\r\n        <li>Reduces stress and anxiety</li>\r\n        <li>Improves focus and concentration</li>\r\n        <li>Enhances emotional regulation</li>\r\n        <li>Better sleep quality</li>\r\n        <li>Increased self-awareness</li>\r\n    </ul>\r\n    \r\n    <h3>Simple Mindfulness Practices</h3>\r\n    \r\n    <h4>1. Mindful Breathing</h4>\r\n    <p>Take 5-10 minutes each day to focus on your breath. Sit comfortably, close your eyes, and pay attention to each inhale and exhale.</p>\r\n    \r\n    <h4>2. Mindful Eating</h4>\r\n    <p>Eat slowly and savor each bite. Pay attention to the taste, texture, and smell of your food.</p>\r\n    \r\n    <h4>3. Mindful Walking</h4>\r\n    <p>When walking, focus on the sensation of your feet touching the ground and the movement of your body.</p>\r\n    \r\n    <h4>4. Body Scan</h4>\r\n    <p>Lie down and mentally scan your body from head to toe, noticing any tension or sensations.</p>\r\n    \r\n    <h3>Incorporating Mindfulness into Daily Routine</h3>\r\n    <p>Start with just 5 minutes a day and gradually increase the time. You can practice mindfulness while doing everyday activities like washing dishes, taking a shower, or waiting in line.</p>\r\n    \r\n    <p>Remember, mindfulness is a skill that develops with practice. Be patient with yourself and enjoy the journey toward greater awareness and peace.</p>', 'Learn how mindfulness can transform your daily life and discover simple practices to incorporate into your routine.', 'assets/images/blog/mindfulness.jpg', 'Mental Health', 'GreenLife Wellness', '2025-06-22 11:07:24', '2025-06-22 11:07:24', 'published'),
(3, 'Nutrition Basics for Optimal Health', '<p>Good nutrition is the foundation of a healthy lifestyle. Understanding the basics of nutrition can help you make better food choices and improve your overall health.</p>\r\n    \r\n    <h3>Essential Nutrients</h3>\r\n    \r\n    <h4>Proteins</h4>\r\n    <p>Proteins are essential for building and repairing tissues. Good sources include lean meats, fish, eggs, legumes, and dairy products.</p>\r\n    \r\n    <h4>Carbohydrates</h4>\r\n    <p>Carbohydrates provide energy for your body. Choose complex carbohydrates like whole grains, fruits, and vegetables over simple sugars.</p>\r\n    \r\n    <h4>Fats</h4>\r\n    <p>Healthy fats are important for brain health and hormone production. Include sources like avocados, nuts, olive oil, and fatty fish.</p>\r\n    \r\n    <h4>Vitamins and Minerals</h4>\r\n    <p>These micronutrients support various bodily functions. Eat a variety of colorful fruits and vegetables to get a wide range of vitamins and minerals.</p>\r\n    \r\n    <h3>Building a Balanced Plate</h3>\r\n    <p>Follow the plate method: fill half your plate with vegetables, one-quarter with lean protein, and one-quarter with whole grains.</p>\r\n    \r\n    <h3>Hydration</h3>\r\n    <p>Water is essential for all bodily functions. Aim to drink at least 8 glasses of water daily, more if you\'re active or in hot weather.</p>\r\n    \r\n    <h3>Meal Planning Tips</h3>\r\n    <ul>\r\n        <li>Plan your meals ahead of time</li>\r\n        <li>Prepare healthy snacks</li>\r\n        <li>Read food labels</li>\r\n        <li>Cook at home more often</li>\r\n        <li>Practice portion control</li>\r\n    </ul>\r\n    \r\n    <h3>Common Nutrition Myths</h3>\r\n    <p>Don\'t believe everything you hear about nutrition. Focus on evidence-based information and consult with healthcare professionals for personalized advice.</p>\r\n    \r\n    <p>Remember, good nutrition is about balance and consistency, not perfection. Small changes in your eating habits can lead to significant improvements in your health.</p>', 'Discover the fundamentals of nutrition and learn how to build a balanced, healthy diet for optimal wellness.', 'assets/images/blog/nutrition.jpg', 'Nutrition', 'GreenLife Wellness', '2025-06-22 11:07:24', '2025-06-22 11:07:24', 'published'),
(4, 'Exercise: The Key to Physical and Mental Wellness', '<p>Regular exercise is one of the most important things you can do for your health. It benefits both your physical and mental well-being in countless ways.</p>\r\n    \r\n    <h3>Physical Benefits</h3>\r\n    <ul>\r\n        <li>Strengthens muscles and bones</li>\r\n        <li>Improves cardiovascular health</li>\r\n        <li>Helps maintain a healthy weight</li>\r\n        <li>Increases flexibility and balance</li>\r\n        <li>Boosts immune function</li>\r\n    </ul>\r\n    \r\n    <h3>Mental Health Benefits</h3>\r\n    <ul>\r\n        <li>Reduces stress and anxiety</li>\r\n        <li>Improves mood and self-esteem</li>\r\n        <li>Enhances cognitive function</li>\r\n        <li>Better sleep quality</li>\r\n        <li>Increases energy levels</li>\r\n    </ul>\r\n    \r\n    <h3>Types of Exercise</h3>\r\n    \r\n    <h4>Cardiovascular Exercise</h4>\r\n    <p>Activities that get your heart rate up, such as walking, running, cycling, or swimming. Aim for at least 150 minutes of moderate-intensity cardio per week.</p>\r\n    \r\n    <h4>Strength Training</h4>\r\n    <p>Exercises that build muscle strength, such as weightlifting, bodyweight exercises, or resistance training. Include strength training 2-3 times per week.</p>\r\n    \r\n    <h4>Flexibility and Balance</h4>\r\n    <p>Activities like yoga, Pilates, or tai chi that improve flexibility, balance, and coordination.</p>\r\n    \r\n    <h3>Getting Started</h3>\r\n    <p>Start slowly and gradually increase intensity and duration. Choose activities you enjoy to make exercise a sustainable habit.</p>\r\n    \r\n    <h3>Exercise Safety</h3>\r\n    <ul>\r\n        <li>Warm up before exercising</li>\r\n        <li>Stay hydrated</li>\r\n        <li>Listen to your body</li>\r\n        <li>Use proper form</li>\r\n        <li>Consult a doctor if you have health concerns</li>\r\n    </ul>\r\n    \r\n    <p>Remember, any amount of exercise is better than none. Start where you are and build from there. Your body and mind will thank you!</p>', 'Explore the comprehensive benefits of exercise and learn how to incorporate physical activity into your daily routine.', 'assets/images/blog/exercise.png', 'Fitness', 'GreenLife Wellness', '2025-06-22 11:07:24', '2025-06-22 11:07:24', 'published'),
(5, 'Stress Management Techniques for Better Health', '<p>Stress is a natural part of life, but chronic stress can have serious effects on your health. Learning effective stress management techniques is essential for maintaining wellness.</p>\r\n    \r\n    <h3>Understanding Stress</h3>\r\n    <p>Stress is your body\'s response to challenges and demands. While some stress can be motivating, chronic stress can lead to health problems including heart disease, depression, and weakened immune function.</p>\r\n    \r\n    <h3>Effective Stress Management Techniques</h3>\r\n    \r\n    <h4>1. Deep Breathing</h4>\r\n    <p>Practice deep breathing exercises to activate your body\'s relaxation response. Try the 4-7-8 technique: inhale for 4 counts, hold for 7, exhale for 8.</p>\r\n    \r\n    <h4>2. Progressive Muscle Relaxation</h4>\r\n    <p>Tense and then relax each muscle group in your body, starting from your toes and working up to your head.</p>\r\n    \r\n    <h4>3. Meditation and Mindfulness</h4>\r\n    <p>Regular meditation practice can help reduce stress and improve emotional regulation. Start with just 5-10 minutes daily.</p>\r\n    \r\n    <h4>4. Physical Activity</h4>\r\n    <p>Exercise is one of the most effective stress relievers. Even a short walk can help clear your mind and reduce stress hormones.</p>\r\n    \r\n    <h4>5. Time Management</h4>\r\n    <p>Learn to prioritize tasks and set realistic goals. Don\'t be afraid to say no to commitments that add unnecessary stress.</p>\r\n    \r\n    <h4>6. Social Support</h4>\r\n    <p>Maintain strong relationships with friends and family. Talking to someone you trust can help you process stress and find solutions.</p>\r\n    \r\n    <h4>7. Healthy Lifestyle</h4>\r\n    <p>Get adequate sleep, eat a balanced diet, and limit caffeine and alcohol, which can exacerbate stress.</p>\r\n    \r\n    <h3>When to Seek Help</h3>\r\n    <p>If stress is interfering with your daily life or you\'re experiencing symptoms of anxiety or depression, consider seeking help from a mental health professional.</p>\r\n    \r\n    <p>Remember, managing stress is a skill that takes practice. Be patient with yourself and celebrate small victories along the way.</p>', 'Learn effective stress management techniques to improve your mental and physical health.', 'assets/images/blog/stress-management.webp', 'Mental Health', 'GreenLife Wellness', '2025-06-22 11:07:24', '2025-06-22 11:07:24', 'published'),
(6, 'The Importance of Quality Sleep for Wellness', '<p>Sleep is not just a time of rest – it\'s a crucial period when your body repairs itself and your brain processes information. Quality sleep is essential for optimal health and wellness.</p>\r\n    \r\n    <h3>Why Sleep Matters</h3>\r\n    <p>During sleep, your body performs vital functions including tissue repair, hormone regulation, memory consolidation, and immune system strengthening.</p>\r\n    \r\n    <h3>Benefits of Quality Sleep</h3>\r\n    <ul>\r\n        <li>Improved memory and learning</li>\r\n        <li>Better mood and emotional regulation</li>\r\n        <li>Enhanced immune function</li>\r\n        <li>Reduced risk of chronic diseases</li>\r\n        <li>Better physical performance</li>\r\n        <li>Improved concentration and productivity</li>\r\n    </ul>\r\n    \r\n    <h3>Sleep Hygiene Tips</h3>\r\n    \r\n    <h4>Create a Sleep Schedule</h4>\r\n    <p>Go to bed and wake up at the same time every day, even on weekends. This helps regulate your body\'s internal clock.</p>\r\n    \r\n    <h4>Optimize Your Sleep Environment</h4>\r\n    <ul>\r\n        <li>Keep your bedroom cool, dark, and quiet</li>\r\n        <li>Invest in a comfortable mattress and pillows</li>\r\n        <li>Use blackout curtains if needed</li>\r\n        <li>Consider white noise machines</li>\r\n    </ul>\r\n    \r\n    <h4>Establish a Bedtime Routine</h4>\r\n    <p>Create a relaxing routine before bed, such as reading, taking a warm bath, or practicing gentle stretching.</p>\r\n    \r\n    <h4>Limit Screen Time</h4>\r\n    <p>Avoid electronic devices at least one hour before bedtime. The blue light can interfere with melatonin production.</p>\r\n    \r\n    <h4>Watch Your Diet</h4>\r\n    <p>Avoid large meals, caffeine, and alcohol close to bedtime. These can disrupt sleep quality.</p>\r\n    \r\n    <h3>Common Sleep Problems</h3>\r\n    <p>If you\'re experiencing persistent sleep problems, consider consulting a healthcare provider. Sleep disorders like insomnia or sleep apnea can have serious health consequences.</p>\r\n    \r\n    <h3>How Much Sleep Do You Need?</h3>\r\n    <p>Most adults need 7-9 hours of sleep per night, but individual needs vary. Pay attention to how you feel during the day to determine your optimal sleep duration.</p>\r\n    \r\n    <p>Remember, quality sleep is an investment in your health and well-being. Prioritize it as you would any other important aspect of your wellness routine.</p>', 'Discover why quality sleep is crucial for wellness and learn practical tips for better sleep hygiene.', 'assets/images/blog/sleep.webp', 'Wellness Tips', 'GreenLife Wellness', '2025-06-22 11:07:24', '2025-06-22 11:07:24', 'published');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','read','replied','closed') NOT NULL DEFAULT 'pending',
  `admin_reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `replied_by_id` int(11) DEFAULT NULL,
  `replied_by_name` varchar(255) DEFAULT NULL,
  `replied_by_type` enum('admin','therapist') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `sender_name`, `sender_email`, `subject`, `message`, `created_at`, `is_read`, `status`, `admin_reply`, `replied_at`, `replied_by_id`, `replied_by_name`, `replied_by_type`) VALUES
(15, 2, 'arkhan shimar', 'arkhansimar1@gmail.com', 'Feedback', 'very good', '2025-06-21 09:17:59', 1, 'replied', 'Thank you', '2025-06-21 09:38:15', 1, 'Admin User', 'admin'),
(16, 2, 'arkhan shimar', 'arkhansimar1@gmail.com', 'Other', 'Hi', '2025-06-21 09:42:34', 1, 'pending', NULL, NULL, NULL, NULL, NULL),
(17, 3, '', '', 'General Inquiry', 'Can i cancel my appointment just 2 days before the scheduled date?', '2025-06-29 11:02:03', 1, 'pending', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` enum('therapy','yoga','beauty','nutrition','wellness program') DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `duration_minutes`, `price`, `category`, `image_path`) VALUES
(1, 'Ayurvedic Massage', 'Traditional full-body massage for relaxation', 45, 5000.00, 'therapy', 'service_1750548171_68573ecb52ba2.jpeg'),
(2, 'Panchakarma Detox', 'Deep detoxification therapy', 120, 12000.00, 'therapy', 'service_1750556250_68575e5a16f7c.jpg'),
(3, 'Yoga Session', 'Group yoga class for all levels', 60, 2000.00, 'yoga', 'service_1750556313_68575e9958e6d.jpg'),
(4, 'Nutrition Counseling', 'Personalized diet and nutrition advice', 45, 3500.00, 'nutrition', 'service_1750556361_68575ec930e62.jpg'),
(5, 'Herbal Facial', 'Herbal facial for glowing skin', 45, 3000.00, 'beauty', 'service_1750556333_68575eadac87e.jpeg'),
(6, 'Stress Relief Pack', 'Combo: massage, yoga, and meditation', 120, 8000.00, 'wellness program', 'service_1750556374_68575ed606e9d.png'),
(7, 'Weight Management', 'Program for healthy weight loss', 60, 7000.00, 'wellness program', 'service_1750556385_68575ee1238a4.png'),
(8, 'Back Pain Therapy', 'Targeted therapy for back pain', 60, 6000.00, 'therapy', 'service_1750556233_68575e4964ea2.jpg'),
(9, 'Detox Juice Cleanse', '7-day detox juice program', 30, 4000.00, 'nutrition', 'service_1750556347_68575ebb299e3.jpg'),
(10, 'Meditation Workshop', 'Guided meditation for stress reduction', 90, 2500.00, 'yoga', 'service_1750556291_68575e833d532.webp'),
(17, 'Aromatherapy Full Body Massage', 'A deeply relaxing massage using essential oils tailored to your needs. Promotes stress relief, improves circulation, and enhances overall well-being.', 60, 8000.00, 'beauty', 'service_1751194422_68611b365841b.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `therapists`
--

CREATE TABLE `therapists` (
  `therapist_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `experience` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapists`
--

INSERT INTO `therapists` (`therapist_id`, `name`, `email`, `password`, `qualification`, `speciality`, `experience`, `profile_pic`, `created_at`, `username`) VALUES
(1, 'Dr. Anil Perera', 'anil.perera@example.com', 'anil123', 'MBBS, MD', 'Ayurveda', '25', 'profile_685761bec6e9a.avif', '2025-06-18 17:22:54', 'anilp'),
(2, 'Dr. Chamara Dissanayake', 'chamara.dissa@example.com', 'chamara123', 'Diploma in Massage Therapy', 'Massage Therapy', '9', 'profile_6857627281062.webp', '2025-06-18 17:22:55', 'chamarad'),
(3, 'Dr. Ishara Wijesinghe', 'ishara.wije@example.com', 'ishara123', 'BAMS', 'Wellness Programs', '5', 'profile_685762a69c7d6.jpg', '2025-06-18 17:22:55', 'isharaw'),
(4, 'Dr. Nadeesha Fernando', 'nadeesha.fernando@example.com', 'nadeesha123', 'MSc Nutrition', 'Nutrition', '6', 'profile_685763c08cb94.jpg', '2025-06-18 17:22:55', 'nadeeshaf'),
(5, 'Dr. Priyanka Silva', 'priyanka.silva@example.com', 'priyanka123', 'BAMS', 'Yoga Therapy', '7', 'profile_6857640668adf.jpg', '2025-06-18 17:22:54', 'priyankas'),
(6, 'Dr. Ruwan Jayasuriya', 'ruwan.jaya@example.com', 'ruwan123', 'BPT', 'Physiotherapy', '8', 'profile_6857641c0f67f.jpg', '2025-06-18 17:22:54', 'ruwanj'),
(7, 'Dr. Sanduni Karunaratne', 'sanduni.karuna@example.com', 'sanduni123', 'BPT', 'Rehabilitation', '4', 'profile_685763d7aa928.jpg', '2025-06-18 17:22:55', 'sandunik');

-- --------------------------------------------------------

--
-- Table structure for table `therapist_availability`
--

CREATE TABLE `therapist_availability` (
  `id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_booked` tinyint(1) DEFAULT 0,
  `appointment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapist_availability`
--

INSERT INTO `therapist_availability` (`id`, `therapist_id`, `available_date`, `start_time`, `end_time`, `is_booked`, `appointment_id`) VALUES
(1, 1, '2025-06-24', '13:00:00', '15:00:00', 1, NULL),
(2, 1, '2025-06-26', '01:00:00', '04:00:00', 1, NULL),
(3, 1, '2025-06-30', '07:00:00', '09:00:00', 1, NULL),
(131, 1, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(132, 1, '2025-06-28', '14:00:00', '15:00:00', 0, NULL),
(133, 1, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(134, 1, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(135, 1, '2025-06-30', '10:00:00', '11:00:00', 0, NULL),
(136, 1, '2025-06-30', '14:00:00', '15:00:00', 0, NULL),
(137, 1, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(138, 1, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(139, 1, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(140, 1, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(141, 1, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(142, 1, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(143, 1, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(144, 1, '2025-07-04', '14:00:00', '15:00:00', 0, NULL),
(145, 2, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(146, 2, '2025-06-28', '14:00:00', '15:00:00', 1, 8),
(147, 2, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(148, 2, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(149, 2, '2025-06-30', '10:00:00', '11:00:00', 1, 7),
(150, 2, '2025-06-30', '14:00:00', '15:00:00', 0, NULL),
(151, 2, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(152, 2, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(153, 2, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(154, 2, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(155, 2, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(156, 2, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(157, 2, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(158, 2, '2025-07-04', '14:00:00', '15:00:00', 0, NULL),
(159, 3, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(160, 3, '2025-06-28', '14:00:00', '15:00:00', 0, NULL),
(161, 3, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(162, 3, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(163, 3, '2025-06-30', '10:00:00', '11:00:00', 0, NULL),
(164, 3, '2025-06-30', '14:00:00', '15:00:00', 0, NULL),
(165, 3, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(166, 3, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(167, 3, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(168, 3, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(169, 3, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(170, 3, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(171, 3, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(172, 3, '2025-07-04', '14:00:00', '15:00:00', 0, NULL),
(173, 4, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(174, 4, '2025-06-28', '14:00:00', '15:00:00', 0, NULL),
(175, 4, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(176, 4, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(177, 4, '2025-06-30', '10:00:00', '11:00:00', 0, NULL),
(178, 4, '2025-06-30', '14:00:00', '15:00:00', 0, NULL),
(179, 4, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(180, 4, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(181, 4, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(182, 4, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(183, 4, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(184, 4, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(185, 4, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(186, 4, '2025-07-04', '14:00:00', '15:00:00', 0, NULL),
(187, 5, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(188, 5, '2025-06-28', '14:00:00', '15:00:00', 0, NULL),
(189, 5, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(190, 5, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(191, 5, '2025-06-30', '10:00:00', '11:00:00', 0, NULL),
(192, 5, '2025-06-30', '14:00:00', '15:00:00', 0, NULL),
(193, 5, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(194, 5, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(195, 5, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(196, 5, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(197, 5, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(198, 5, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(199, 5, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(200, 5, '2025-07-04', '14:00:00', '15:00:00', 0, NULL),
(201, 6, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(202, 6, '2025-06-28', '14:00:00', '15:00:00', 0, NULL),
(203, 6, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(204, 6, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(205, 6, '2025-06-30', '10:00:00', '11:00:00', 0, NULL),
(206, 6, '2025-06-30', '14:00:00', '15:00:00', 0, NULL),
(207, 6, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(208, 6, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(209, 6, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(210, 6, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(211, 6, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(212, 6, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(213, 6, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(214, 6, '2025-07-04', '14:00:00', '15:00:00', 0, NULL),
(215, 7, '2025-06-28', '10:00:00', '11:00:00', 0, NULL),
(216, 7, '2025-06-28', '14:00:00', '15:00:00', 0, NULL),
(217, 7, '2025-06-29', '10:00:00', '11:00:00', 0, NULL),
(218, 7, '2025-06-29', '14:00:00', '15:00:00', 0, NULL),
(219, 7, '2025-06-30', '10:00:00', '11:00:00', 0, NULL),
(220, 7, '2025-06-30', '14:00:00', '15:00:00', 1, 9),
(221, 7, '2025-07-01', '10:00:00', '11:00:00', 0, NULL),
(222, 7, '2025-07-01', '14:00:00', '15:00:00', 0, NULL),
(223, 7, '2025-07-02', '10:00:00', '11:00:00', 0, NULL),
(224, 7, '2025-07-02', '14:00:00', '15:00:00', 0, NULL),
(225, 7, '2025-07-03', '10:00:00', '11:00:00', 0, NULL),
(226, 7, '2025-07-03', '14:00:00', '15:00:00', 0, NULL),
(227, 7, '2025-07-04', '10:00:00', '11:00:00', 0, NULL),
(228, 7, '2025-07-04', '14:00:00', '15:00:00', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `user_type` enum('client','therapist','admin') NOT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `first_name`, `last_name`, `date_of_birth`, `phone`, `address`, `user_type`, `registration_date`, `last_login`, `profile_pic`, `status`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$RYPiMqCO3u3WlzrlMZxnLe4KEFqU3sqC.g3RJxdtfObDJPBSl2rRG', 'Admin', 'User', '1990-01-01', '', 'colombo', 'admin', '2025-06-18 18:29:44', '2025-06-18 22:49:55', NULL, 'active'),
(2, 'arkhan', 'arkhansimar1@gmail.com', '$2y$10$4Tl2joQtOEvEME4O6et.WusNGFP8.RO2TtvYbIogivZ/j3VCXs1ky', 'arkhan', 'shimar', '2004-09-22', '0761006149', 'Mawanella', 'client', '2025-06-18 11:41:20', '2025-06-18 22:08:53', 'profile_685658b64fc0a.jpg', 'active'),
(3, 'sahl', 'sahl@gmail.com', '$2y$10$qSjV7Bm0oJYbYU6vBPgXq.HtBBv/Lu6Zu4sxSMcX6AUxhj4c6PurC', 'sahl', 'mhd', '1995-12-15', '0761234567', 'kegalle', 'client', '2025-06-19 12:02:25', NULL, NULL, 'active'),
(120, 'ihsan', 'ihsan@gmail.com', '$2y$10$Lb/ZgLqpfUXbY5vP2lLEDerT6Q5LT6R1qEb.y8auHTVKJuyRpfasO', 'Ihsan', 'Mhd', '2025-07-02', '', 'kandy', 'client', '2025-06-29 15:00:30', NULL, NULL, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `therapist_id` (`therapist_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `replied_by_id` (`replied_by_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `therapists`
--
ALTER TABLE `therapists`
  ADD PRIMARY KEY (`therapist_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_therapist` (`therapist_id`),
  ADD KEY `fk_appointment` (`appointment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `therapists`
--
ALTER TABLE `therapists`
  MODIFY `therapist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`replied_by_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  ADD CONSTRAINT `fk_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
