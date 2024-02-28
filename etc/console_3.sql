SELECT count(mm_user.id) as AnzahlBenutzer FROM mealmatch_local.mm_user;
# Wie viele sind davon aktiv geworden? (Als Gastgeber und Gast)

# Meals
SELECT count(meal.host_id), meal.status FROM
  mealmatch_local.meal
GROUP BY meal.status;

# JoinRequest
SELECT DISTINCT mm_user.username, meal_id, count(meal_id), host_id, mm_user.id
  FROM mealmatch_local.join_request
    LEFT JOIN mealmatch_local.meal ON join_request.meal_id = meal.id
    LEFT JOIN mealmatch_local.mm_user ON meal.host_id = mm_user.id
GROUP BY username;


# Tickets
SELECT count(DISTINCT mm_user.id) as AnzahlGastgeber, mm_user.username, meal_ticket.titel as HostedMeal
  FROM mealmatch_local.mm_user
    LEFT JOIN mealmatch_local.m_m_user_profile ON mm_user.profile_id = m_m_user_profile.id
    LEFT JOIN mealmatch_local.meal_ticket ON mm_user.id = meal_ticket.created_by_id
  GROUP BY meal_ticket.titel;

# JoinRequests
SELECT count(join_request.id) as SummeGastanfragen FROM mealmatch_local.join_request;
# Wie hoch ist der durschnittliche Umsatz pro Gast/ Teilnehmer?

# Wie viele Meals sind bisher ausgerichtet worden?
SELECT count(meal.id) FROM
  mealmatch_local.meal
WHERE meal.status LIKE '%FINISHED%';
# Wie viele Gäste haben insgesamt an den Meals teilgenommen?
# Joinrequest

# Gäste und deren Tickets
SELECT
  DISTINCT count(mm_user.username) as "Gäste(summe gast)", sum(meal_ticket.sharedCosts) as "Ticket Betrag", sum(meal_ticket.mmFee) as "Ticket Umsatz"
FROM mealmatch_local.meal_ticket
  LEFT JOIN mealmatch_local.mm_user ON meal_ticket.Ticket_Guest_id = mm_user.id
WHERE meal_ticket.status LIKE '%PAYED%';
# Gäste und deren Tickets
SELECT
  DISTINCT mm_user.username as Gast, count(meal_ticket.id), sum(meal_ticket.sharedCosts), sum(meal_ticket.mmFee)
FROM mealmatch_local.meal_ticket
  LEFT JOIN mealmatch_local.mm_user ON meal_ticket.Ticket_Guest_id = mm_user.id
WHERE meal_ticket.status LIKE '%PAYED%'
GROUP BY username;
# Übersicht alle verkauften tickets
SELECT
  AVG(sharedCosts) as "DurchschnittKostenbeitrag"
FROM mealmatch_local.meal_ticket
WHERE
  meal_ticket.status LIKE '%payed%'
  OR
  meal_ticket.status LIKE '%PAYMENT_SUCCESS%';

SELECT
  AVG(mmFee) as "DurchschnittGebühr"
FROM mealmatch_local.meal_ticket
WHERE
  meal_ticket.status LIKE '%payed%'
  OR
  meal_ticket.status LIKE '%PAYMENT_SUCCESS%';


# Wie viele Gäste sind wiederkehrende Gäste geworden?
SELECT
  mm_user.username as "Gast", count(meal_ticket.id)
FROM mealmatch_local.meal_ticket
  LEFT JOIN mealmatch_local.mm_user ON meal_ticket.Ticket_Guest_id = mm_user.id
WHERE
  meal_ticket.status LIKE '%payed%'
  OR
  meal_ticket.status LIKE '%PAYMENT_SUCCESS%'
GROUP BY username;
# JoinRequest ...
SELECT count(DISTINCT mm_user.username) as 'Wiederkehrende', count(DISTINCT join_request.meal_id) as 'C'
  FROM mealmatch_local.join_request
    LEFT JOIN mealmatch_local.mm_user ON join_request.created_by_id = mm_user.id
 WHERE join_request.accepted = 1 AND 'C' >= 0;

SELECT join_request.*
FROM mealmatch_local.join_request
  LEFT JOIN mealmatch_local.mm_user ON join_request.created_by_id = mm_user.id
WHERE join_request.accepted = 1;

# Wie häufig hat ein aktiver Gast durschnittlich an Meals teilgenommen?
SELECT avg(C) FROM
(SELECT DISTINCT mm_user.username as 'Wiederkehrende', count(DISTINCT join_request.meal_id) as 'C'
FROM mealmatch_local.join_request
  LEFT JOIN mealmatch_local.mm_user ON join_request.created_by_id = mm_user.id
WHERE join_request.accepted = 1 AND 'C' >= 0
GROUP BY username) nested;

# Wie viele Profile sind vollständig befüllt?
SELECT mm_user.*, m_m_user_profile.* FROM mealmatch_local.mm_user
LEFT JOIN mealmatch_local.m_m_user_profile ON mm_user.profile_id = m_m_user_profile.id
WHERE enabled = 1
AND payPalEmail != ''
AND phone != ''
AND selfDescription != ''
AND hobbies != '';

SELECT u.username, u.email, u.enabled, p.gender, p.firstName, p.lastName, p.city, p.areaCode FROM mm_user as u
  LEFT JOIN m_m_user_profile as p
    ON p.id = u.profile_id;