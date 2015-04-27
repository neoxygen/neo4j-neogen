(user:User {login:username, name:lastName} *20)-[:TWEETED *1..n]->(tweet:Tweet {text:sentence} *40)
(tweet)-[:HAS_TAG *n..1]->(tag:Tag {word:word} *20)
(user)-[:FOLLOW *n..n]->(user)
(user)-[:RETWEETED *n..n]->(retweet:Retweet *20)-[:RETWEET_OF *n..1]->(tweet)