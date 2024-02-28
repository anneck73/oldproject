# Wie viele User haben wir z.Z.?
SELECT count(mm_user.id) as AnzahlBenutzer FROM mealmatch_local.mm_user WHERE enabled = 1;
# Wie viele sind davon aktiv geworden? (Als Gastgeber und Gast)

# Wie hoch ist der durschnittliche Umsatz pro Gast/ Teilnehmer?
# Wie viele Meals sind bisher ausgerichtet worden?
# Wie viele Gäste haben insgesamt an den Meals teilgenommen?
# Wie viele Gäste sind wiederkehrende Gäste geworden?
# Wie häufig hat ein aktiver Gast durschnittlich an Meals teilgenommen?
# Wie viele Profile sind vollständig befüllt?
SELECT count(mm_user.id) as ProfilVollständig FROM mealmatch_local.mm_user
  LEFT JOIN mealmatch_local.m_m_user_profile ON mm_user.profile_id = m_m_user_profile.id
WHERE enabled = 1
      AND payPalEmail != ''
      AND phone != ''
      AND selfDescription != ''
      AND hobbies != '';
