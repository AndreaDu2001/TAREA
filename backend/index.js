"use strict";
import dotenv from "dotenv";
import express from "express";
import cors from "cors";
import { createClient } from "redis";
import mysql from "mysql2/promise";

dotenv.config();
// environment variables
const expressPort = process.env.PORT || 5001;

// redis
const redisUsername = process.env.REDIS_USERNAME || "";
const redisPassword = process.env.REDIS_PASSWORD || "";
const redisHost = process.env.REDIS_HOST || "";
const redisPort = process.env.REDIS_PORT || "";
const redisChannel = process.env.REDIS_CHANNEL || "";

// mysql
const sqlHost = process.env.MYSQL_HOST || "";
const sqlUser = process.env.MYSQL_USERNAME || "";
const sqlPassword = process.env.MYSQL_PASSWORD || "";
const sqlDatabase = process.env.MYSQL_DATABASE || "";
const sqlTable = process.env.MYSQL_TABLE || "";

// configs
const redisUrl = `redis://${redisUsername}:${redisPassword}@${redisHost}:${redisPort}`;
const dbConfig = {
  host: sqlHost,
  user: sqlUser,
  password: sqlPassword,
  database: sqlDatabase,
};

const redisClient = createClient({ url: redisUrl });

const getData = async () => {
  const sqlQuery = `SELECT data FROM ${sqlTable}`;
  const sqlConnection = await mysql.createConnection(dbConfig);
  return sqlConnection.execute(sqlQuery);
};
// Redis helpers made tolerant if no redis configured or connection fails
const safeRedisConnect = async () => {
  if (!redisHost) return false;
  try {
    if (!redisClient.isOpen) await redisClient.connect();
    return true;
  } catch (err) {
    console.log("Redis connect error:", err && err.message ? err.message : err);
    return false;
  }
};

const setRedisCache = async (jsonData) => {
  try {
    const ok = await safeRedisConnect();
    if (!ok) return null;
    const value = JSON.stringify({ isCached: "yes", data: jsonData });
    await redisClient.set("key", value);
    await redisClient.disconnect();
    return true;
  } catch (err) {
    console.log("setRedisCache error:", err && err.message ? err.message : err);
    try { await redisClient.disconnect(); } catch (e) {}
    return null;
  }
};

const getRedisCache = async () => {
  try {
    const ok = await safeRedisConnect();
    if (!ok) return null;
    const cachedData = await redisClient.get("key");
    await redisClient.disconnect();
    return cachedData;
  } catch (err) {
    console.log("getRedisCache error:", err && err.message ? err.message : err);
    try { await redisClient.disconnect(); } catch (e) {}
    return null;
  }
};

const deleteRedisCache = async () => {
  try {
    const ok = await safeRedisConnect();
    if (!ok) return null;
    await redisClient.del("key");
    await redisClient.disconnect();
    return true;
  } catch (err) {
    console.log("deleteRedisCache error:", err && err.message ? err.message : err);
    try { await redisClient.disconnect(); } catch (e) {}
    return null;
  }
};

const publishToRedis = async (data) => {
  try {
    const ok = await safeRedisConnect();
    if (!ok) return 0;
    const subscriberCount = await redisClient.publish(redisChannel, data);
    await redisClient.disconnect();
    return subscriberCount;
  } catch (err) {
    console.log("publishToRedis error:", err && err.message ? err.message : err);
    try { await redisClient.disconnect(); } catch (e) {}
    return 0;
  }
};

//express
const app = express();
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: false }));

// express endpoints
app.get("/", (_, res) => res.status(200).send("connected to server 1!"));
app.get("/data", async (_, res) => {
  try {
    const cachedData = await getRedisCache();
    if (cachedData) {
      const results = JSON.parse(cachedData);
      res.status(200).json({ message: "success", ...results });
      // ending the fn
      return;
    }

    const [data, _] = await getData();
    await setRedisCache(data);

    res.status(200).json({ message: "success", isCached: "no", data });
  } catch (error) {
    console.log({ error });
    res.status(500).json({ message: "failure", error });
  }
});

app.post("/create", async (req, res) => {
  const { data } = req.body;
  console.log(req.body);
  try {
    if (!data) throw new Error("missing data");
    const subscriberCount = await publishToRedis(data);
    console.log({ subscriberCount });
    const test = await deleteRedisCache();
    res.status(200).json({ message: "success" });
  } catch (error) {
    console.log({ error });
    res.status(500).json({ message: "failure", error });
  }
});

app.listen(expressPort, () => console.log(`served on port ${expressPort}`));
