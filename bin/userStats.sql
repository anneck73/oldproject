USE 'mealmatch_local';
SELECT
  u.username,
  u.email,
  u.enabled,
  p.gender,
  p.firstName,
  p.lastName,
  p.city,
  p.areaCode,
  u.created_at
FROM mm_user AS u
  LEFT JOIN m_m_user_profile AS p
    ON p.id = u.profile_id;