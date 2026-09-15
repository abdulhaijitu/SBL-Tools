INSERT
    OR IGNORE INTO roles (
        id,
        name,
        display_name,
        description
    )
VALUES (
        5,
        'demo',
        'Demo User',
        'Guest demo account with read-only access'
    );

INSERT
    OR
REPLACE INTO
    users (
        id,
        name,
        email,
        password,
        phone,
        designation,
        status,
        created_at,
        updated_at
    )
VALUES (
        2,
        'মোঃ রফিকুল ইসলাম',
        '01700000001@sbl.internal',
        '$2b$10$YkqdVHTq7SoYTNt68HRPo.1GEM/KA9Vs9o1DiMUxP0S099yTh0RUq',
        '01700000001',
        'Senior Associate (Member)',
        'active',
        unixepoch (),
        unixepoch ()
    ),
    (
        3,
        'ডেমো পার্টনার ইউজার',
        '01700000003@sbl.internal',
        '$2b$10$YkqdVHTq7SoYTNt68HRPo.1GEM/KA9Vs9o1DiMUxP0S099yTh0RUq',
        '01700000003',
        'Guest Prospective Partner (Demo)',
        'active',
        unixepoch (),
        unixepoch ()
    );

DELETE FROM user_roles WHERE user_id IN (2, 3);

INSERT INTO user_roles (user_id, role_id) VALUES (2, 4), (3, 5);