const express = require("express");
const dotenv = require("dotenv");
const path = require("path");
const cookieParser = require("cookie-parser");
const reqParser = require("request-parser")

const { notFoundHandler,errorHandler, clientErrorHandler,logErrors} =  require("./middleware/common/errorHandaller");
const route = require("./Route/route");

const app = express();
dotenv.config();

// // db connection
// mongoose.connect(process.env.MONGO_CONNECTION_STRING, {
//     useNewUrlParser:true,
//     useUnifiedTopology:true,
// })
// .then(()=> console.log("database connection successful"))
// .catch(error => console.log(error));


// request parsers
app.use(express.json());
app.use(express.urlencoded({ extended: true}));


// set view engine
app.set("view engine", "ejs");

// // set static folder
// app.use(express.static(path.join(__dirname, "public")));

// parse cookies
app.use(cookieParser(process.env.COOKIE_SECRET));

// routing setup

app.use("/", route)

// common handler
// 404 not found handling
app.use(notFoundHandler);
app.use(clientErrorHandler);
app.use(logErrors)
app.use(errorHandler);


app.listen(process.env.APP_PORT, () => {
    console.log(`app listening to port ${process.env.APP_PORT}`);
})