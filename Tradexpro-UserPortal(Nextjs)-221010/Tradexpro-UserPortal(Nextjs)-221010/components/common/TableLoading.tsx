import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const TableLoading = () => {
  return (
    <div className="asset-balances-left">
      <div className="section-wrapper">
        <div className="table-responsive">
          <table
            id="assetBalances"
            className="table table-borderless secendary-table asset-balances-table"
          >
            <thead>
              <tr>
                <th scope="col ">
                  <Skeleton
                    width="100%"
                    className="mb-2"
                    height={40}
                    count={1}
                    borderRadius={10}
                  />
                </th>
                <th scope="col ">
                  <Skeleton
                    width="100%"
                    className="mb-2"
                    height={40}
                    count={1}
                    borderRadius={10}
                  />
                </th>
                <th scope="col ">
                  <Skeleton
                    width="100%"
                    className="mb-2"
                    height={40}
                    count={1}
                    borderRadius={10}
                  />
                </th>
                <th scope="col ">
                  <Skeleton
                    width="100%"
                    className="mb-2"
                    height={40}
                    count={1}
                    borderRadius={10}
                  />
                </th>
                <th scope="col ">
                  <Skeleton
                    width="100%"
                    className="mb-2"
                    height={40}
                    count={1}
                    borderRadius={10}
                  />
                </th>
              </tr>
            </thead>
            <tbody>
              <tr id="" className="odd">
                <td>
                  <Skeleton
                    width="100%"
                    height={30}
                    count={10}
                    borderRadius={10}
                  />
                </td>
                <td>
                  <Skeleton
                    width="100%"
                    height={30}
                    count={10}
                    borderRadius={10}
                  />
                </td>
                <td>
                  <Skeleton
                    width="100%"
                    height={30}
                    count={10}
                    borderRadius={10}
                  />
                </td>
                <td>
                  <Skeleton
                    width="100%"
                    height={30}
                    count={10}
                    borderRadius={10}
                  />
                </td>
                <td>
                  <Skeleton
                    width="100%"
                    height={30}
                    count={10}
                    borderRadius={10}
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default TableLoading;
