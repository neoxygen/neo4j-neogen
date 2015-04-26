// Example :
(person:Person {firstname: firstName, lastname: lastName } *20)-[:KNOWS *n..n]->(person)
(person)-[:HAS *n..n]->(skill:Skill {name: word} *15)
(company:Company {name: company, desc: catchPhrase} *10)-[:LOOKS_FOR_COMPETENCE *n..n]->(skill)
(company)-[:LOCATED_IN *n..1]->(country:Country {name: country} *25)
(person)-[:LIVES_IN *n..1]->(country)